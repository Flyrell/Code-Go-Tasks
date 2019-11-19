<?php

namespace App\Normalizer;

use ArrayObject;
use DateTime;
use DateTimeInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Serializer;

class NormalizerHelper
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $data
     * @param array $keys
     */
    public function denormalizeDatetimes(array &$data, array $keys)
    {
        foreach ($keys as $date) {
            if (isset($data[$date]) && !$data[$date] instanceof DateTimeInterface) {
                try {
                    $data[$date] = new DateTime($data[$date]);
                } catch (Exception $e) {
                    throw new UnexpectedValueException(sprintf('Invalid date value "%s" for "%s"', $data[$date], $date), null, $e);
                }
            }
        }
    }

    /**
     * @param array  $data
     * @param string $key
     * @param string $type
     */
    public function denormalizeObject(array &$data, string $key, string $type)
    {
        if (isset($data[$key])) {
            try {
                $data[$key] = $this->serializer->denormalize($data[$key], $type);
            } catch (RuntimeException $e) {
                throw new UnexpectedValueException(null, null, $e);
            }
        }
    }

    /**
     * @param array  $data
     * @param string $key
     * @param string $type
     */
    public function denormalizeArrayOfObjects(array &$data, string $key, string $type)
    {
        if (isset($data[$key])) {
            try {
                $data[$key] = new ArrayObject($this->serializer->denormalize($data[$key], $type.'[]'));
            } catch (RuntimeException $e) {
                throw new UnexpectedValueException(null, null, $e);
            }
        }
    }
}
