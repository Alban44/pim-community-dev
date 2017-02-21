<?php

namespace Pim\Bundle\ApiBundle\Stream;

use Pim\Component\Catalog\Validator\UniqueValuesSet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Read the php input as a stream, line by line.
 * Each line represents the content of the standardized product, and will be forwarded as the content
 * of a subrequest.
 * Each subrequest will return itw own response, which is streamed to the client.
 *
 * The response will be returned as a stream, each line corresponding to the response of its own subrequest.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductStreamUpdater
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var HttpKernelInterface */
    protected $httpKernel;

    /** @var UniqueValuesSet */
    protected $uniqueValuesSet;

    /** @var int */
    protected $bufferSize;

    /**
     * @param RequestStack        $requestStack
     * @param HttpKernelInterface $httpKernel
     * @param UniqueValuesSet     $uniqueValuesSet
     * @param int                 $bufferSize
     */
    public function __construct(
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel,
        UniqueValuesSet $uniqueValuesSet,
        $bufferSize
    ) {
        $this->requestStack    = $requestStack;
        $this->httpKernel      = $httpKernel;
        $this->uniqueValuesSet = $uniqueValuesSet;
        $this->bufferSize      = $bufferSize;
    }

    /**
     * @param Request $request       master request containing the whole data to process
     * @param string  $controller    the controller name
     * @param array   $identifierKey identifier key of the entity
     *
     * @return StreamedResponse
     */
    public function buildStreamResponse(Request $request, $controller, $identifierKey)
    {
        set_time_limit(3600);
        //max_input_time(3600);

        $response = new StreamedResponse();

        $response->setCallback(function () use ($request, $controller, $identifierKey) {
            $streamContent = $request->getContent(true);

            $lineNumber = 1;
            $line = true;
            do {
                try {
                    $line = $this->readInputBuffer($streamContent);
                    if (false === $line) {
                        continue;
                    }
                    $data = $this->getDecodedContent($line);

                    if (!isset($data[$identifierKey]) || '' === trim($data[$identifierKey])) {
                        throw new UnprocessableEntityHttpException('Identifier is missing.');
                    }

                    $response = [
                        'line'         => $lineNumber,
                        $identifierKey => $data[$identifierKey],
                    ];

                    $subResponse = $this->forward($controller, ['code' => $data[$identifierKey]], $line);

                    if ('' !== $subResponse->getContent()) {
                        $response =  array_merge($response, json_decode($subResponse->getContent(), true));
                    } else {
                        $response['code'] = $subResponse->getStatusCode();
                    }
                } catch (HttpException $e) {
                    $response = [
                        'line'    => $lineNumber,
                        'code'    => $e->getStatusCode(),
                        'message' => $e->getMessage(),
                    ];
                }

                $this->uniqueValuesSet->reset();
                $this->flushOutputBuffer($response);
                $lineNumber++;
            } while (false !== $line);
        });

        return $response;
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function getDecodedContent($content)
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller    The controller name (a string like BlogBundle:Post:index)
     * @param array  $uriParameters uri parameters of the controller
     * @param string $content       content of the subrequest
     *
     * @return Response A Response instance
     */
    public function forward($controller, array $uriParameters, $content)
    {
        $parameters = array_merge(['_controller' => $controller], $uriParameters);
        $subRequest = new Request([], [], $parameters, [], [], [], $content);
        $subRequest->setRequestFormat('json');
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    /**
     * Read a line from a stream.
     * If the line is too long fot the buffer, consume the rest of the line
     * and throws an error 400.
     *
     * @param $streamContent
     *
     * @throws BadRequestHttpException
     * @return string
     */
    protected function readInputBuffer($streamContent)
    {
        $buffer = stream_get_line($streamContent, $this->bufferSize + 1, PHP_EOL);
        $bufferSizeExceeded = strlen($buffer) > $this->bufferSize;

        while (strlen($buffer) > $this->bufferSize) {
            $buffer = stream_get_line($streamContent, $this->bufferSize + 1, PHP_EOL);
        }

        if ($bufferSizeExceeded) {
            throw new BadRequestHttpException("Line is too long.");
        }

        return $buffer;
    }

    /**
     * Flush the buffer with the content encoded with JSON.
     * A carriage return is added to separate the content from the next line.
     *
     * @param $content
     */
    protected function flushOutputBuffer($content)
    {
        echo json_encode($content).PHP_EOL;
        ob_flush();
        flush();
    }

    // * Copy the php input into a file.
    // * It avoids to customize the max_input_time parameter in the php configuration
    // * and allows more control over the uploaded stream data.
    // *
    // * @param $request
    // *
    // * @throws BadRequestHttpException
    // *
    // * @return string
    // */
    //protected function streamInputToFile(Request $request)
    //{
    //    $streamInput = $request->getContent(true);
    //
    //}
}
