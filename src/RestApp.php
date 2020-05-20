<?php

namespace SlimRestful;

use Minime\Annotations\AnnotationsBag;
use Minime\Annotations\Reader;
use Psr\Container\ContainerInterface;
use SimpleXMLElement;
use Slim\App;
use Slim\Routing\Route;
use Slim\Routing\RouteCollectorProxy;

class RestApp extends App {
    
    /**
     * Add slim middlewares
     * 
     * @return RestApp
     */
    public function addSlimMiddlewares(): RestApp {
        $this->addRoutingMiddleware(); //To use middlewares
        $this->addErrorMiddleware(true, true, true); //To return exact error code when throwing slim exceptions
        return $this;
    }


    /**
     * Add middlewares to the $route
     * 
     * @param Route $route                        The route
     * @param array|SimpleXMLElement $middlewares List of middlewares
     * @param string $middlewaresNamespace        Namespace of middlewares
     * @param AnnotationsBag $annos               List of method annotations
     * 
     * @return void
     */
    protected function addMiddlewares(Route $route, $middlewares, string $middlewaresNamespace, AnnotationsBag $annos): void {

        foreach($middlewares as $middleware) {

            if(is_null($middleware['middleware'])) {
                throw new RoutesLoadingException('Middlewares loading: middleware attribute is missing');
            }

            if($middleware instanceof SimpleXMLElement) {
                $middlewareAttributes = $middleware->attributes();
                $middleware = array();
                foreach($middlewareAttributes as $key => $value) {
                    $middleware[$key] = (string) $value;
                }
            }
            
            $isReversed = array_key_exists('reversed', $middleware) ? $middleware['reversed'] : null;
            $annotation = array_key_exists('annotation', $middleware)
                ? $annos->has($middleware['annotation'])
                    ? $annos->get($middleware['annotation'])
                    : false
                : null
            ;

            if(
                is_null($annotation)
                || !is_null($annotation) && $annotation
                || !is_null($annotation) && $isReversed && !$annotation
            ) {
                $route->add($middlewaresNamespace . $middleware['middleware']);
            }
        }
    }



    /**
     * Load routes from xml or json file
     * 
     * @param string $filename JSON or XML file name (Example: 'routes.json')
     * 
     * @return RestApp
     * 
     * @throws RoutesLoadingException
     */
    public function loadRoutes(string $filename): RestApp {
        
        if (\file_exists($filename)) {
            $extension = pathinfo($filename)['extension'];
            $routes = null;
            $middlewares = null;
            
            switch ($extension) {
                case 'xml':
                    $file                  = simplexml_load_file($filename);
                    $routesXmlElement      = $file->routes;
                    $middlewaresXmlElement = $file->middlewares;

                    if(!is_null($routesXmlElement)) {
                        $routes               = $routesXmlElement->children();
                        $controllersNamespace = $routesXmlElement->attributes()['namespace'];
                    }
                    if(!is_null($middlewaresXmlElement)) {
                        $middlewares          = $middlewaresXmlElement->children();
                        $middlewaresNamespace = $middlewaresXmlElement->attributes()['namespace'];
                    }
                    break;
                case 'json':
                    $file                  = json_decode(file_get_contents($filename), true);
                    $routesXmlElement      = $file['routes'];
                    $middlewaresXmlElement = $file['middlewares'];

                    if(!is_null($routesXmlElement)) {
                        $routes               = $routesXmlElement['list'];
                        $controllersNamespace = $routesXmlElement['namespace'];
                    }
                    if(!is_null($middlewaresXmlElement)) {
                        $middlewares          = $middlewaresXmlElement['list'];
                        $middlewaresNamespace = $middlewaresXmlElement['namespace'];
                    }
                    break;
                default:
                    throw new RoutesLoadingException('Routes file must be of xml or json type');
                    break;
                }

            $container = $this->getContainer();
            $reader = Reader::createFromDefaults();

            if(is_null($routes)) {
                throw new RoutesLoadingException('No routes in file');
            }

            foreach($routes as $route) {

                $routesAttributes = $extension === 'xml' ? $route->attributes() : $route;
                $controller = $controllersNamespace . $routesAttributes['controller'];
                $methodsAllowed = array('get', 'post', 'put', 'patch', 'delete');
                $self = $this;

                $this->group(
                    $routesAttributes['pattern'],
                    function(RouteCollectorProxy $group) use (
                        $self,
                        $methodsAllowed,
                        $controllersNamespace,
                        $routesAttributes,
                        $middlewares,
                        $middlewaresNamespace,
                        $reader
                    ) {
                        $controller = $controllersNamespace . $routesAttributes['controller'];
                        $name = $routesAttributes['name'];
                        $controllerMethods = get_class_methods($controller);

                        foreach($methodsAllowed as $method) {
                            if(in_array($method, $controllerMethods)) {
                                $methodRoute = $group->$method(
                                    $method === 'get' ? '[/{id}]' : '',
                                    $controller . ':' . $method
                                )->setName($name . strtoupper($method));
                                
                                if(!is_null($middlewares)) {
                                    $annos = $reader->getMethodAnnotations($controller, $method);
                                    $self->addMiddlewares($methodRoute, $middlewares, $middlewaresNamespace, $annos);
                                }
                            }

                        }

                    }
                );

                $container->set($controller, function (ContainerInterface $c) use ($controller) {
                    return new $controller();
                });
            }
        } else {
            throw new RoutesLoadingException('Routes file loading failed.');
        }

        return $this;
    }
}
