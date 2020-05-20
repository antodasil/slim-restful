<?php

namespace SlimRestful;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Server\RequestHandlerInterface as IRequestHandler;
use Psr\Http\Message\ResponseInterface as IResponse;
use Slim\Psr7\Response;

class BaseMiddleware {

    /**
     * Add $before before controller response and call $func after
     * 
     * @param Request $request
     * @param RequestHandler $handler
     * @param String $before
     * @param callable $func
     * 
     * @return Response
     */
    protected function doAfter(IRequest $request, IRequestHandler $handler, String $before, callable $func): IResponse {

        $response = $handler->handle($request);
        $content  = (string) $response->getBody();

        if(is_null($res = $func())) {
            $res = '';
        }

        $new = new Response();
        $new->getBody()->write($before . $content . (String) $res);
        $new->withStatus($response->getStatusCode());
        foreach($response->getHeaders() as $key => $value) {
            $response->withHeader($key, $value);
        }
        $new->withProtocolVersion($response->getProtocolVersion());

        return $new;
    }
}
