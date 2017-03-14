<?php

/**
 * Created by PhpStorm.
 * User: maksimbelov
 * Date: 22.01.17
 * Time: 16:12
 */
class AdminRouter
{
    private $routes;

    public function __construct()
    {
        $routePath = ROOT.ADMIN_PATH . "/config/routes.php";
        $this->routes = include ($routePath);
    }

    private function getURI()
    {
        if(!empty($_SERVER['REQUEST_URI']))
        {
            $internalPath =  str_replace("mtn-admin", "", $_SERVER['REQUEST_URI']);
            return trim($internalPath,'/');
        }
    }

    public function run()
    {
        $uri = $this->getURI();

        $result  = null;
        foreach($this->routes as $URIpattern => $path)
        {
            if(preg_match("~^$URIpattern~",$uri))
            {
                $internalRoute = preg_replace("~^$URIpattern~",$path,$uri);

                $segments = explode('/',$internalRoute);

                $controllerName = ucfirst(array_shift($segments) . 'Controller');
                $actionName = 'action' . ucfirst(array_shift($segments));
                $parameters = $segments;

                $controllerFile = ROOT.ADMIN_PATH . '/controllers/' . $controllerName . '.php';

                if(file_exists($controllerFile))
                {
                    include_once ($controllerFile);
                }
                else
                {
                    include_once (ROOT.ADMIN_PATH . '/controllers/ErrorController.php');
                    $result = ErrorController::actionError500("File $controllerName does not exist in system");
                    break;
                }

                $controllerObject = new $controllerName;

                if(method_exists($controllerObject,$actionName))
                {
                    $result = call_user_func_array(array($controllerObject,$actionName),$parameters);
                    if($result){
                        break;
                    }
                    else
                    {
                        include_once (ROOT.ADMIN_PATH . '/controllers/ErrorController.php');
                        $result = ErrorController::actionError404();
                        break;
                    }
                }
                else
                {
                    include_once (ROOT.ADMIN_PATH . '/controllers/ErrorController.php');
                    $result = ErrorController::actionError500("Method $actionName in $controllerName does not exist");
                    break;
                }
            }
        }

        if($result == null)
        {
            include_once (ROOT.ADMIN_PATH . '/controllers/ErrorController.php');
            $result = ErrorController::actionError404();
        }
    }
}