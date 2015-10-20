TraverzMenu
===========

Control pro Nette Framework usnadňující tvorbu menu a drobečkové navigace,
využívající traverzování nad stromy a poskytující administrační funkce

Autor: Petr Poupě
Licence: MIT

Použití
-------

Továrnička v presenteru:

	protected function createComponentMenu() {
		$nav = new Pupek\TraverzMenu;
                return $menu;
	}


Menu v šabloně:

        {control menu:tree}
        {control menu:tree pocet_urovni}


Drobečková navigace v šabloně:
        
        {control menu:breadcrumbs}
        {control menu:breadcrumbs separator}


Site mapa:

        {control menu:map}
        {control menu:map pocet_urovni}