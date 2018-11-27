<?php
namespace Expertime\Iclient\Plugin\Metadata\Form;

class Image
{
    protected $validImage;

    public function __construct(
        \Expertime\Iclient\Model\Source\Validation\Image $validImage
    ) {
        $this->validImage = $validImage;
    }

    /**
     * {@inheritdoc}
     *
     * @return ImageContentInterface|array|string|null
     */
    public function beforeExtractValue(\Magento\Customer\Model\Metadata\Form\Image $subject, $value)
    {
        $attrCode = $subject->getAttribute()->getAttributeCode();

        if ($this->validImage->isImageValid('tmp_name', $attrCode) === false) {
            $_FILES[$attrCode]['tmpp_name'] = $_FILES[$attrCode]['tmp_name'];
            unset($_FILES[$attrCode]['tmp_name']);
        }

        return [];
    }
}
