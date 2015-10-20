<?php

namespace Acl;

/**
 * Permission model of access control list
 *
 * @author Petr Poupě
 */
class Permission extends \Nette\Security\Permission
{

	public function __construct()
	{
		// definujeme role
		$this->setRoles();
		// seznam zdrojů, ke kterým mohou uživatelé přistupovat
		$this->setResources();
		// pravidla, určující, kdo co může s čím dělat - defaultně vše zakázáno
		$this->setPrivileges();
	}

	private function setRoles()
	{
		$this->addRole('guest');
		$this->addRole('user', 'guest'); // user dědí od guest

		/** Only for CompanyModule! */
		$this->addRole('company', 'guest');

		// forum roles
		$this->addRole('contributor', 'user'); // only can contribute
		$this->addRole('editor', 'contributor');

		// admin roles
		$this->addRole('admin', 'editor');
		$this->addRole('superadmin', 'admin'); // supervisor dědí od admin
	}

	private function setResources()
	{
		$this->addResource('category'); // categories in forum
		$this->addResource('forum'); // forums in forum
		$this->addResource('topic'); // topics in forum
		$this->addResource('post'); // posts in forum

		$this->addResource('candidates'); // Candidates module
                
		$this->addResource('cv'); // CV generator

		$this->addResource('content'); // page content (blog, etc.)

		$this->addResource('dashboard'); // dashboard

		$this->addResource('account'); // user settings

		$this->addResource('backend'); // admin section

		$this->addResource('KCFinder'); // admin section

		$this->addResource('service'); // service actions (only for top users)
		$this->addResource('xml'); // service actions (only for top users)
	}

	private function setPrivileges()
	{
		/**
		 * VIEW - view page
		 * ADD - add new items
		 * ALLOW - allow new items
		 * EDIT - can edit
		 * DELETE - can delete
		 */

		$this->deny('guest');

		$this->allow('user', 'cv', 'view');
		$this->allow('user', 'dashboard', 'view');
		$this->allow('user', 'account', 'edit');
		$this->allow('user', 'KCFinder', 'view');
                
		$this->allow('company', 'candidates'); // all acces to candidates

		// forum privileges
//        $this->allow('contributor', 'category', 'show');
		$this->allow('contributor', 'forum', 'view');
		$this->allow('contributor', 'topic', 'add');
		$this->allow('contributor', 'post', 'add');

//        $this->allow('editor', 'category');
		$this->allow('editor', 'forum');
		$this->allow('editor', 'topic', 'delete');
		$this->allow('editor', 'post', 'allow');
		$this->allow('editor', 'post', 'delete');

		// admin privileges
		$this->allow('admin', 'content'); // all
		$this->allow('admin', 'backend'); // all
		$this->allow('admin', 'KCFinder', 'edit');
//        $this->allow('admin', 'backend', 'access'); // access to admin section

		$this->allow('superadmin'); // všechna práva a zdroje pro super-administrátora
	}
}
