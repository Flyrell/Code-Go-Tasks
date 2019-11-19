<?php

namespace App\Normalizer;

use App\Entity\Skill;
use App\Enum\SkillLevel;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SkillNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @var ObjectNormalizer
     */
    protected $normalizer;

    /**
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param Skill $skill
     */
    public function normalize($skill, $format = null, array $context = [])
    {
        $normalizeToOptions = $context['options'] ?? false;

        if ($normalizeToOptions) {
            return [
                'value' => $skill->getId(),
                'label' => sprintf('%s - %s', $skill->getName(), SkillLevel::getLabel($skill->getLevel())),
            ];
        }

        return $this->normalizer->normalize($skill, $format, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Serializer\Normalizer\NormalizerInterface::supportsNormalization()
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Skill;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->normalizer->denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->resolveDocumentClassName(Skill::class) === $type;
    }
}
