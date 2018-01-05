<?php
namespace Hillrange\Security\Entity;

/**
 * Failure
 */
class Failure
{
	/**
	 * @var integer
	 */
	private $id;

	/**
	 * @var string
	 */
	private $address;

	/**
	 * @var integer
	 */
	private $failures;

	/**
	 * @var \DateTime
	 */
	private $failureTime;

	/**
	 * Construct
	 *
	 * @return    Failure
	 */
	public function __construct($address = null)
	{
		$this->failures     = 0;
		$this->setAddress($address);

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get address
	 *
	 * @return string
	 */
	public function getAddress(): ?string
	{
		return $this->address;
	}

	/**
	 * Set address
	 *
	 * @param string $address
	 *
	 * @return Failure
	 */
	public function setAddress($address = null): Failure
	{
		if (! empty($address))
			if (empty(preg_match('#^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$#', $address)))
				throw new \InvalidArgumentException('The IP address is not valid. %s', $address);

		$this->address = $address;

		return $this;
	}

	/**
	 * Get count
	 *
	 * @return integer
	 */
	public function getFailures(): int
	{
		if (empty($this->failures))
			$this->failures = 0;

		return $this->failures;
	}

	/**
	 * Set failures
	 *
	 * @param \number $count
	 *
	 * @return Failure
	 */
	public function setFailures(int $failures = 0): Failure
	{
		$this->failures = intval($failures);

		return $this;
	}

	/**
	 * Inc count
	 *
	 * @return    Failure
	 */
	public function incFailures()
	{
		$this->failures++;

		return $this->setFailureTime();
	}

	/**
	 * @return \DateTime
	 */
	public function getFailureTime(): \DateTime
	{
		if (empty($this->failureTime))
			$this->setFailureTime(new \DateTime('-21 minutes'));
		return $this->failureTime;
	}

	/**
	 * @param \DateTime $failureTime
	 *
	 * @return null|Failure
	 */
	public function setFailureTime(\DateTime $failureTime = null): Failure
	{
		if (is_null($failureTime))
			$failureTime = new \DateTime('now');
		$this->failureTime = $failureTime;

		return $this;
}
}
