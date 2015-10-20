<?php

use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route,
    Nette\Application\Routers\SimpleRouter;

/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @return Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $router[] = new Route('index.php', 'Front:Homepage:default', Route::ONE_WAY);

//        $lang = NULL;
        $lang = "en";

        $router[] = $companyRouter = new RouteList('Company');
        $companyRouter[] = new Route('company/<presenter>/<action>[/<id>]', array(
            'presenter' => "Homepage",
            'action' => "default",
            'lang' => $lang,
            'id' => NULL,
        ));
        
        $router[] = $fotoRouter = new RouteList('CompanyProfile');
        $fotoRouter[] = new Route('company-profile/<slug>', array(
            'presenter' => "Homepage",
            'action' => "default",
            'lang' => $lang,
        ));

        $router[] = $fotoRouter = new RouteList('Profile');
        $fotoRouter[] = new Route('profile/<token>', array(
            'presenter' => "Homepage",
            'action' => "default",
            'lang' => $lang,
        ));
        
        $router[] = $fotoRouter = new RouteList('Foto');
        $fotoRouter[] = new Route('foto/<size \d+\-\d+>/<name .+>', array(
            'presenter' => "Foto",
            'action' => "default",
            'size' => NULL,
            'name' => NULL,
        ));

        $router[] = $serviceRouter = new RouteList('Service');
//        $serviceRouter[] = new Route('tools/[<lang \w{2}>/]<presenter>/<action>[/<id>]', array(
        $serviceRouter[] = new Route('tools/<presenter>/<action>[/<id>]', array(
            'presenter' => "Default",
            'action' => "default",
            'lang' => $lang,
            'id' => NULL,
        ));

        $router[] = $adminRouter = new RouteList('Admin');
        $adminRouter[] = new Route('admin/[<lang \w{2}>/]<presenter>/<action>[/<id>]', array(
            'presenter' => "Default",
            'action' => "default",
            'lang' => NULL,
            'id' => NULL,
        ));

        $router[] = $frontRouter = new RouteList('Front');
//        $frontRouter[] = new Route('[<lang \w{2}>/]blog/list', array(
        $frontRouter[] = new Route('blog/list', array(
            'presenter' => "Blog",
            'action' => "list",
            'lang' => $lang,
            'id' => NULL,
        ));
//        $frontRouter[] = new Route('[<lang \w{2}>/]blogs', array(
        $frontRouter[] = new Route('blogs', array(
            'presenter' => "Blog",
            'action' => "list",
            'lang' => $lang,
            'id' => NULL,
        ));
//        $frontRouter[] = new Route('[<lang \w{2}>/]blog[/<id>]', array(
        $frontRouter[] = new Route('blog[/<id>]', array(
            'presenter' => "Blog",
            'action' => "default",
            'lang' => $lang,
            'id' => NULL,
        ));
//        $frontRouter[] = new Route('[<lang \w{2}>/]<presenter>/<action>[/<id>]', array(
        $frontRouter[] = new Route('<presenter>/<action>[/<id>]', array(
            'presenter' => "Homepage",
            'action' => "default",
            'lang' => $lang,
            'id' => NULL,
        ));

        return $router;
    }

}
