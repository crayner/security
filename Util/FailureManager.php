<?php
namespace Hillrange\Security\Util;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\Failure;
use Hillrange\Security\Repository\FailureRepository;
use Symfony\Component\Routing\RouterInterface;

class FailureManager
{
	/**
	 * @var FailureRepository
	 */
	private $failureRepository;

	/**
	 * @var EntityManagerInterface
	 */
	private $om;

	/**
	 * @var Failure
	 */
	private $failure;

	/**
	 * FailureManager constructor.
	 *
	 * @param EntityManagerInterface $om
	 * @param RouterInterface        $router
	 */
	public function __construct(EntityManagerInterface $om)
	{
		$this->failureRepository = $om->getRepository(Failure::class);
		$this->om             = $om;
	}

	/**
	 * @param null $ip
	 *
	 * @return bool
	 */
	public function isIPBlocked($ip = null)
	{
		if (! $ip) return true;

		$this->failure = $this->failureRepository->findOneBy(['address' => $ip]);

		if (is_null($this->failure)) return false;

		$dt = new \DateTime('-20 Minutes');

		if ($this->failure->getFailures() >= 3 && $this->failure->getFailureTime()->getTimestamp() >= $dt->getTimestamp())
			return true;

		if ($this->failure->getFailureTime()->getTimestamp() < $dt->getTimestamp())
		{
			$this->om->remove($this->failure);
			$this->om->flush();
		}

		return false;
	}
}