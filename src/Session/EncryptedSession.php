<?php


    declare(strict_types = 1);


    namespace WPEmerge\Session;

    use WPEmerge\Contracts\EncryptorInterface;
    use WPEmerge\ExceptionHandling\Exceptions\DecryptException;
    use WPEmerge\ExceptionHandling\Exceptions\EncryptException;

    class EncryptedSession extends Session
    {
        /**
         * @var EncryptorInterface
         */
        protected $encryptor;


        public function __construct($name, SessionDriver $handler, EncryptorInterface $encryptor, int $strength = 24)
        {
            $this->encryptor = $encryptor;

            parent::__construct($name, $handler, $strength);
        }

        /**
         * Prepare the raw string data from the session for unserialization.
         *
         * @param  string  $data
         *
         * @return string
         */
        protected function prepareForUnserialize( string $data) : string
        {
            try {
                return $this->encryptor->decrypt($data);
            } catch (DecryptException $e) {
                return serialize([]);
            }
        }

        /**
         * Prepare the serialized session data for storage.
         *
         * @param  string  $data
         *
         * @return string
         * @throws EncryptException
         */
        protected function prepareForStorage(string $data) : string
        {
            return $this->encryptor->encrypt($data);
        }


    }