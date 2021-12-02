<?php

declare(strict_types=1);

namespace Tests\integration\eloquent;

use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Codeception\TestCase\WPTestCase;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Snicco\Database\WPEloquentStandalone;
use Illuminate\Database\Eloquent\Collection;
use Tests\integration\eloquent\fixtures\Models\City;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\integration\eloquent\Concerns\WithTestTables;
use Tests\integration\eloquent\fixtures\Models\Country;
use Tests\integration\eloquent\Concerns\WPDBTestHelpers;
use Tests\integration\eloquent\fixtures\Models\Activity;
use Tests\integration\eloquent\fixtures\Factories\CountryFactory;

class DatabaseFactoriesTest extends WPTestCase
{
    
    use WPDBTestHelpers;
    use WithTestTables;
    
    protected function setUp() :void
    {
        parent::setUp();
        Container::setInstance();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Model::unsetEventDispatcher();
        Model::unsetConnectionResolver();
        
        ($eloquent = new WPEloquentStandalone())->bootstrap();
        $eloquent->withDatabaseFactories(
            'Tests\\integration\\eloquent\\fixtures\\Models\\',
            'Tests\\integration\\eloquent\\fixtures\\Factories\\',
        );
        $this->withDatabaseExceptions();
        DB::table('countries')->delete();
        DB::beginTransaction();
    }
    
    protected function tearDown() :void
    {
        DB::rollBack();
        parent::tearDown();
    }
    
    /** @test */
    public function a_factory_can_be_created()
    {
        $factory = Country::factory();
        
        $this->assertInstanceOf(CountryFactory::class, $factory);
    }
    
    /** @test */
    public function a_factory_can_create_a_model()
    {
        $country = Country::factory()->make();
        
        $this->assertInstanceOf(Country::class, $country);
    }
    
    /** @test */
    public function testFactoryStates()
    {
        $country = Country::factory()->narnia()->make();
        
        $this->assertInstanceOf(Country::class, $country);
        $this->assertSame('Narnia', $country->continent);
    }
    
    /** @test */
    public function testFactoryMultiple()
    {
        $country = Country::factory()->count(3)->make();
        
        $this->assertInstanceOf(Collection::class, $country);
        $this->assertCount(3, $country);
        $country->each(function ($country) {
            $this->assertInstanceOf(Country::class, $country);
        });
    }
    
    /** @test */
    public function testWithAttributeOverride()
    {
        $country = Country::factory()->make([
            'name' => 'My country',
        ]);
        
        $this->assertInstanceOf(Country::class, $country);
        $this->assertSame('My country', $country->name);
    }
    
    /** @test */
    public function testMakeDoesNotPersistModel()
    {
        $country = Country::factory()->make();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordNotExists(['name' => $country->name]);
        $table->assertTotalCount(0);
    }
    
    /** @test */
    public function testCreatePersistsModel()
    {
        $country = Country::factory()->create();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['name' => $country->name]);
    }
    
    /** @test */
    public function testCreateManyPersistsMany()
    {
        $countries = Country::factory()->count(3)->create();
        
        $table = $this->assertDbTable('wp_countries');
        
        $countries->each(function ($country) use ($table) {
            $table->assertRecordExists(['name' => $country->name]);
        });
        
        $table->assertTotalCount(3);
    }
    
    /** @test */
    public function testCreateWithAttributeOverride()
    {
        $country = Country::factory()->create([
            'continent' => 'Narnia',
        ]);
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['name' => $country->name, 'continent' => 'Narnia']);
    }
    
    /** @test */
    public function testWithSequence()
    {
        $countries = Country::factory()
                            ->count(6)
                            ->state(
                                new Sequence(
                                    ['continent' => 'Narnia'],
                                    ['continent' => 'Westeros'],
                                )
                            )
                            ->create();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertTotalCount(6);
        
        $countries_in_narnia = $countries->filter(function ($country) {
            return $country->continent === 'Narnia';
        });
        
        $countries_in_westeros = $countries->filter(function ($country) {
            return $country->continent === 'Westeros';
        });
        
        $this->assertCount(3, $countries_in_narnia);
        $this->assertCount(3, $countries_in_westeros);
    }
    
    /** @test */
    public function testHasMany()
    {
        $country = Country::factory()
                          ->has(City::factory()->count(3))
                          ->create();
        
        $this->assertInstanceOf(Country::class, $country);
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['id' => $country->id]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $country->id], 3);
    }
    
    /** @test */
    public function testHasManyWithMagicMethod()
    {
        $country = Country::factory()
                          ->hasCities(3)
                          ->create();
        
        $this->assertInstanceOf(Country::class, $country);
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['id' => $country->id]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $country->id], 3);
    }
    
    /** @test */
    public function testHasManyWithCustomAttributes()
    {
        $country = Country::factory()
                          ->hasCities(3, function (array $attributes, Country $country) {
                              return ['population' => 10];
                          })
                          ->create();
        
        $this->assertInstanceOf(Country::class, $country);
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['id' => $country->id]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $country->id, 'population' => 10], 3);
    }
    
    /** @test */
    public function testBelongsTo()
    {
        $cities = City::factory()
                      ->count(3)
                      ->for(
                          Country::factory()->state([
                              'name' => 'Narnia',
                          ])
                      )
                      ->create();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['name' => $cities[0]->country->name]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $cities[0]->country->id], 3);
    }
    
    /** @test */
    public function testBelongsToWithMagicMethod()
    {
        $cities = City::factory()
                      ->count(3)
                      ->forCountry([
                          'name' => 'Narnia',
                      ])
                      ->create();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['name' => $cities[0]->country->name]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $cities[0]->country->id], 3);
    }
    
    /** @test */
    public function testManyToMany()
    {
        $city = City::factory()
                    ->has(Activity::factory()->count(3))
                    ->create();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['name' => $city->country->name]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $city->country->id], 1);
        
        $table = $this->assertDbTable('wp_activities');
        $table->assertTotalCount(3);
        
        $table = $this->assertDbTable('wp_activity_city');
        $table->assertCountWhere(['city_id' => $city->id], 3);
    }
    
    /** @test */
    public function testManyToManyWithAttached()
    {
        $city = City::factory()
                    ->hasAttached(
                        Activity::factory()->count(3),
                        ['popularity' => 10]
                    )
                    ->create();
        
        $table = $this->assertDbTable('wp_activities');
        $table->assertTotalCount(3);
        
        $table = $this->assertDbTable('wp_activity_city');
        $table->assertCountWhere(['city_id' => $city->id, 'popularity' => 10], 3);
    }
    
    /** @test */
    public function testHasAttachedWithExisting()
    {
        $countries = Activity::factory()
                             ->count(4)
                             ->create();
        
        $city = City::factory()
                    ->hasAttached($countries)
                    ->create();
        
        $table = $this->assertDbTable('wp_activities');
        $table->assertTotalCount(4);
        
        $table = $this->assertDbTable('wp_activity_city');
        $table->assertCountWhere(['city_id' => $city->id], 4);
    }
    
    /** @test */
    public function testManyToManyWithMagicMethod()
    {
        $city = City::factory()
                    ->hasActivities(3)
                    ->create();
        
        $table = $this->assertDbTable('wp_countries');
        $table->assertRecordExists(['name' => $city->country->name]);
        $table->assertTotalCount(1);
        
        $table = $this->assertDbTable('wp_cities');
        $table->assertCountWhere(['country_id' => $city->country->id], 1);
        
        $table = $this->assertDbTable('wp_activities');
        $table->assertTotalCount(3);
        
        $table = $this->assertDbTable('wp_activity_city');
        $table->assertCountWhere(['city_id' => $city->id], 3);
    }
    
}



