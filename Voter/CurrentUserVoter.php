<?php
namespace Hillrange\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserVoter implements VoterInterface
{
	/**
	 * @param TokenInterface $token
	 * @param int:UserInterface          $subject
	 * @param array          $attributes
	 *
	 * @return int
	 */
	public function vote(TokenInterface $token, $subject, array $attributes)
	{
		$result = VoterInterface::ACCESS_ABSTAIN;

		if (!in_array('IS_CURRENT_USER', $attributes))
			return $result;

		if (intval($subject) == $subject || $subject instanceof UserInterface)
		{

			$user = $token->getUser();

			if (! $user instanceof UserInterface)
				return $result;

			if (intval($subject) == $subject)
			{
				if ($subject == $user->getId())
					return VoterInterface::ACCESS_GRANTED;
				else
					return VoterInterface::ACCESS_DENIED;
			}

			if ($user->isEqualTo($subject))
				return VoterInterface::ACCESS_GRANTED;
			else
				return VoterInterface::ACCESS_DENIED;
		}

		return VoterInterface::ACCESS_ABSTAIN;
	}
}