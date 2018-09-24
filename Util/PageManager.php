<?php
namespace Hillrange\Security\Util;

use Doctrine\ORM\EntityManagerInterface;
use Hillrange\Security\Entity\Page;
use Hillrange\Security\Repository\PageRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PageManager
 * @package Hillrange\Security\Util
 */
class PageManager
{
    /**
     * @var RequestStack 
     */
	private $stack;

	/**
	 * @var EntityManagerInterface
	 */
	private $om;

	/**
	 * @var array
	 */
	private $pageSecurity;

	/**
	 * @var Page
	 */
	private $page;

	/**
	 * @var RouterInterface
	 */
	private $router;

    /**
     * PageManager constructor.
     * @param EntityManagerInterface $om
     * @param RouterInterface $router
     * @param RequestStack $stack
     */
	public function __construct(EntityManagerInterface $om, RouterInterface $router, RequestStack $stack)
	{
		$this->stack          = $stack;
		$this->om             = $om;
		$this->router         = $router;
	}

	/**
	 * Find One by Route
	 *
	 * @param string       $routeName
	 * @param array|string $attributes
	 *
	 * @return Page
	 */
	public function findOneByRoute(string $routeName, $attributes = []): ?Page
	{
	    if (! $this->isKeepPageCount())
	        return null;

		$this->pageSecurity = $this->getSession()->get('pageSecurity');

		if (!is_array($this->pageSecurity))
			$this->pageSecurity = [];

		$this->page = empty($this->pageSecurity[$routeName]) ? $this->getPageRepository()->loadOneByRoute($routeName) : $this->pageSecurity[$routeName];
		
		$this->page = $this->page instanceof Page ? $this->page : new Page();

		if (!is_array($attributes))			$attributes = [$attributes];

		foreach ($attributes as $attribute)
			$this->page->addRole($attribute);

		if (empty($this->page->getId()))
		{
			if (!is_null($this->router->getRouteCollection()->get($routeName)))
			{
				$this->page->setCacheTime();
				$this->getOm()->persist($this->page);
				$this->getOm()->flush();
				$this->pageSecurity[$routeName] = $this->page;
			}
			else
				return $this->page;
		}

		if ($this->page->getCacheTime() < new \DateTime('-15 Minutes'))
		{
			foreach ($attributes as $attribute)
				$this->page->addRole($attribute);

			$this->page->setCacheTime();
			$this->getOm()->persist($this->page);
			$this->getOm()->flush();
			$this->pageSecurity[$routeName] = $this->page;
		}

		$this->getSession()->set('pageSecurity', $this->pageSecurity);

		return $this->page;
	}

	/**
	 * @return PageRepository|null
	 */
	public function getPageRepository(): ?PageRepository
	{
	    if (! $this->isKeepPageCount())
	        return null;
		return $this->getOm()->getRepository(Page::class);
	}

    /**
     * @return EntityManagerInterface
     */
    public function getOm(): EntityManagerInterface
    {
        return $this->om;
    }

    /**
     * @var bool
     */
    private $keepPageCount = true;

    /**
     * @return bool
     */
    public function isKeepPageCount(): bool
    {
        return $this->keepPageCount;
    }

    /**
     * @param bool $keepPageCount
     * @return PageManager
     */
    public function setKeepPageCount(bool $keepPageCount): PageManager
    {
        $this->keepPageCount = $keepPageCount;
        return $this;
    }

    /**
     * getSession
     *
     * @return null|SessionInterface
     */
    private function getSession(): ?SessionInterface
    {
        if($this->stack->getCurrentRequest()->hasSession())
            return $this->stack->getCurrentRequest()->getSession();
        return null;
    }
}