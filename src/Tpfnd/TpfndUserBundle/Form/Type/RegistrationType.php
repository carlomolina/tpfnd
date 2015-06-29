<?php

namespace Tpfnd\TpfndUserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder->add('user', new TpfndUserType());
	$builder->add(
			'terms', 'checkbox', array('property_path' => 'termsAccepted')
	);
	$builder->add('Register', 'submit');
  }

  public function getName() {
	return 'registration';
  }
}
