<?php

namespace XHGui\Controller;

use Exception;
use InvalidArgumentException;
use Slim\Http\Request;
use Slim\Slim;
use XHGui\Saver\SaverInterface;
use XHGui\AbstractController;

class ImportController extends AbstractController
{
    /**
     * @var SaverInterface
     */
    private $saver;

    /** @var string */
    private $token;

    public function __construct(Slim $app, SaverInterface $saver, $token)
    {
        parent::__construct($app);
        $this->saver = $saver;
        $this->token = $token;
    }

    public function import()
    {
        $request = $this->app->request();
        $response = $this->app->response();

        try {
            $id = $this->runImport($request);
            $result = ['ok' => true, 'id' => $id, 'size' => $request->getContentLength()];
        } catch (InvalidArgumentException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $response->setStatus(401);
        } catch (Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $response->setStatus(500);
        }

        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($result));
    }

    private function runImport(Request $request): string
    {
        if ($this->token) {
            if ($this->token !== $request->get('token')) {
                throw new InvalidArgumentException('Token validation failed');
            }
        }

        $data = json_decode($request->getBody(), true);
        return $this->saver->save($data);
    }
}
