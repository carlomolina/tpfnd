<?php

namespace Tpfnd\TpfndUserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TpfndUserType extends AbstractType {
  public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder->add('firstname', 'text');
	$builder->add('lastname', 'text');
	$builder->add('email', 'email');
	$builder->add('password', 'repeated', array(
		'first_name' => 'password',
		'second_name' => 'confirm',
		'type' => 'password'
	));
  }

  public function configureOptions(OptionsResolver $resolver) {
	$resolver->setDefaults(array(
		'data_class' => 'Tpfnd\TpfndUserBundle\Entity\TpfndUser'
	));
  }

  public function getName() {
	return 'user';
  }
}
