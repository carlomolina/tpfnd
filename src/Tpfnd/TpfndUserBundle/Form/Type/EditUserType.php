<?php

namespace Tpfnd\TpfndUserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EditUserType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder->add('firstname', 'text');
	$builder->add('lastname', 'text');
	$builder->add('email', 'email');
	$builder->add('Edit', 'submit');
	$builder->add('email', null, array('disabled' => true));
  }

  public function getName() {
	return 'editUser';
  }
}
