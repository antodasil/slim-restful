<?php

namespace SlimRestful;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Slim\Exception\HttpBadRequestException;

/**
 * BaseController abstract class
 */
abstract class BaseController {

    //HTTP status codes
    const GET_SUCCESS_STATUS    = 200; // OK
    const POST_SUCCESS_STATUS   = 201; // CREATED
    const PUT_SUCCESS_STATUS    = 201; // CREATED
    const DELETE_SUCCESS_STATUS = 204; // No Content

    //Default error messages
    const BAD_REQUEST_EXCEPTION    = 'Bad request';
    const MISSING_PARAMS_EXCEPTION = 'Missing parameters';
    const MISSING_ARGS_EXCEPTION   = 'Missing arguments';

    protected SettingsManager $settings;

    //Controller __construct
    public function __construct() {
        $this->settings = SettingsManager::getInstance();
    }

    /**
     * Throw an error if required POST params are missing
     * 
     * @param IRequest $request
     * @param array $params string array
     * @param string $message
     * 
     * @return array post parameters list
     * 
     * @throws HttpBadRequestException
     */
    protected function requiredPostParams(IRequest $request, array $params, string $message = self::MISSING_PARAMS_EXCEPTION): array {
        
        if(!empty($params)) {

            $postParams = $request->getParsedBody();

            if(is_null($postParams)) {
                $this->isBadRequest($request, $message);
            }
    
            foreach($params as $param) {
                if(is_null($postParams[$param])) {
                    $this->isBadRequest($request, $message);
                }
            }
        }

        return $postParams;
    }

    /**
     * Throw an error if required GET params are missing
     * 
     * @param Request $request
     * @param array $params string array
     * @param string $message
     * 
     * @return array GET params list
     * 
     * @throws HttpBadRequestException
     */
    protected function requiredGetParams(IRequest $request, array $params, string $message = self::MISSING_PARAMS_EXCEPTION): array {
        
        if(!empty($params)) {

            $getParams = $request->getQueryParams();
    
            if(is_null($getParams)) {
                $this->isBadRequest($request, $message);
            }
    
            foreach($params as $param) {
                if(is_null($getParams[$param])) {
                    $this->isBadRequest($request, $message);
                }
            }
        }

        return $getParams;
    }

    /**
     * Throw an error if required routes arguments are missing
     * 
     * @param Request $request
     * @param array $args URI arguments
     * @param array $params string array
     * @param string $message
     * 
     * @return array $args
     * 
     * @throws HttpBadRequestException
     */
    protected function requiredArguments(
        IRequest $request, array $args, array $params, string $message = self::MISSING_ARGS_EXCEPTION
    ): array {

        if(!empty($params)) {

            if(empty($args)) {
                $this->isBadRequest($request, $message);
            }
    
            foreach($params as $param) {
                if(is_null($args[$param])) {
                    $this->isBadRequest($request, $message);
                }
            }
        }

        return $args;
    }

    /**
     * Throw an HttpBadRequestException
     * 
     * @param ServerRequestInterface $request
     * @param string $message Exception message
     * 
     * @return void
     * 
     * @throws HttpBadRequestException
     */
    protected function isBadRequest(IRequest $request, string $message = self::BAD_REQUEST_EXCEPTION): void {
        throw new HttpBadRequestException($request, $message);
    }
}