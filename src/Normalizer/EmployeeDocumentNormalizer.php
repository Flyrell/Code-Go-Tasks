<?php

namespace App\Normalizer;

use App\Document\AddressDocument;
use App\Document\EmployeeDocument;
use App\Document\PaymentDocument;
use App\Document\SkillDocument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EmployeeDocumentNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var ObjectNormalizer
     */
    protected $normalizer;

    /**
     * @var NormalizerHelper
     */
    protected $helper;

    /**
     * @param ObjectNormalizer $normalizer
     * @param NormalizerHelper $helper
     */
    public function __construct(ObjectNormalizer $normalizer, NormalizerHelper $helper)
    {
        $this->normalizer = $normalizer;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     *
     * @param EmployeeDocument $employee
     */
    public function normalize($employee, $format = null, array $context = [])
    {
        return $this->normalizer->normalize($employee, $format, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Serializer\Normalizer\NormalizerInterface::supportsNormalization()
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EmployeeDocument;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $data['id'] = (int) $data['id'];

        $this->helper->denormalizeArrayOfObjects($data, 'payments', $this->resolveDocumentClassName(PaymentDocument::class));
        $this->helper->denormalizeArrayOfObjects($data, 'addresses', $this->resolveDocumentClassName(AddressDocument::class));
        $this->helper->denormalizeArrayOfObjects($data, 'skills', $this->resolveDocumentClassName(SkillDocument::class));

        $employees = $this->normalizer->denormalize($data, $class, $format, $context);

        return $employees;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->resolveDocumentClassName(EmployeeDocument::class) === $type;
    }
}
