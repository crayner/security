<?php
namespace Hillrange\Security\Voter;

use Hillrange\Security\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * RoleVoter votes if any attribute starts with a given prefix.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GroupVoter implements VoterInterface
{
	private $prefix;
	private $groupList;

	public function __construct(array $groupList, string $prefix = 'ROLE_')
	{
		$this->prefix = $prefix;
		$this->groupList = $groupList;
	}

	/**
	 * {@inheritdoc}
	 */
	public function vote(TokenInterface $token, $subject, array $attributes)
	{
		$result = VoterInterface::ACCESS_ABSTAIN;
		$roles = $this->extractRoles($token);

		foreach ($attributes as $attribute) {
			if ($attribute instanceof Role) {
				$attribute = $attribute->getRole();
			}

			if (!is_string($attribute) || 0 !== strpos($attribute, $this->prefix)) {
				continue;
			}

			$result = VoterInterface::ACCESS_DENIED;
			foreach ($roles as $role) {
				if ($attribute === $role->getRole()) {
					return VoterInterface::ACCESS_GRANTED;
				}
			}
		}

		return $result;
	}

	/**
	 * @param TokenInterface $token
	 *
	 * @return array
	 */
	protected function extractRoles(TokenInterface $token)
	{
		$user = $token->getUser();

		$roles = [];

		if (! $user instanceof User)
			return $roles;

		$user->setGroupList($this->groupList);

		foreach($user->getRoles(true) as $role)
			$roles[] = new Role($role);

		return $roles;
	}
}
