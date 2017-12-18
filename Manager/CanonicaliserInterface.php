<?php
namespace HillRange\Security\Manager;

interface CanonicaliserInterface
{
	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function canonicalise($string);
}
