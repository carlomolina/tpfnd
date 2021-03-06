<?php

namespace Tpfnd\TpfndUserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EditPasswordType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder->add('oldpassword', 'password');
	$builder->add('newpassword', 'repeated', array(
		'first_name' => 'newpassword',
		'second_name' => 'confirm',
		'type' => 'password'
	));
	$builder->add('Submit', 'submit');
  }

  public function getName() {
	return 'editPassword';
  }
}
