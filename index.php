<?php

require_once __DIR__ . '/vendor/autoload.php';

use Squiz\PhpCodeExam\Logger;
use Squiz\PhpCodeExam\Searcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$searcher = new Searcher();
$logger = new Logger();

$logger->logMsg(
    sprintf('Got request %s', $request->getUri()),
    'request'
);

try {
    if (preg_match('/contents/', $path) !== 0) {
        if ($request->query->get('term') == null) {
            $response = new JsonResponse(['data' => false], Response::HTTP_BAD_REQUEST);
        } else {
            $term = $request->query->get('term');
            $response = new JsonResponse(
                ['data' => (new Searcher())->execute($term, $type = 'content')],
                Response::HTTP_OK
            );
        }
    }
    else if (preg_match('/tags/', $path) !== 0) {
        if ($request->query->get('term') == null) {
            $response = new JsonResponse(['data' => false], Response::HTTP_BAD_REQUEST);
        } else {
            $term = $request->query->get('term');
            $response = new JsonResponse(
                ['data' => (new Searcher())->execute($term, 'tags')],
                Response::HTTP_OK
            );
        }
    } else if (preg_match('/pages/', $path) !== 0) {
        $paths = explode('/', $path);
        $id = $paths[2];
        if ($id == null) {
            $response = new JsonResponse(['data' => false], Response::HTTP_BAD_REQUEST);
        } else {
            $result = (new Searcher())->getPageById($id);
            $response = new JsonResponse(['data' => $result],
                $result ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_NOT_FOUND);
        }
    } else {
        // print all (is it needed ??)
        $data = $searcher->getAllData();

        $response = empty($data) ?
            new Response(NULL, Response::HTTP_NO_CONTENT) :
            new JsonResponse(['data' => $data], Response::HTTP_ACCEPTED);
    }

    $response->send();
    $logger->logMsg(
        sprintf('Sent response %s', $response->getContent()),
        'response'
    );
} catch (Exception $ex) {
    $response = new JsonResponse(['exception' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
    $response->send();
    $logger->logMsg(
        sprintf('%s: %s', $ex->getCode(), $ex->getMessage())
    );
}