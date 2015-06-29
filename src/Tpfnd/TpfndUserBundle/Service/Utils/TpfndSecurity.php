<?php
namespace Tpfnd\TpfndUserBundle\Service\Utils;

use Symfony\Component\Security\Core\Security;

final class TpfndSecurity extends Security
{
    const LAST_EMAIL = '_security.last_email';
}
