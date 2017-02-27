<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Validator\Constraints\NotNullProperties;
use Pim\Component\Catalog\AttributeTypes;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotNullPropertiesValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\NotNullPropertiesValidator');
    }

    function it_validates_not_blank_property_value(
        $context,
        NotNullProperties $constraint,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => 'not_blank_value']);
        $value
            ->getType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    function it_does_not_validate_blank_property_value(
        $context,
        NotNullProperties $constraint,
        ConstraintViolationBuilderInterface $violationBuilder,
        Attribute $value
    ) {
        $constraint->properties = ['my_property'];

        $value
            ->getProperties()
            ->willReturn(['my_property' => null]);
        $value
            ->getType()
            ->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder
            ->atPath('properties')
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }
}
