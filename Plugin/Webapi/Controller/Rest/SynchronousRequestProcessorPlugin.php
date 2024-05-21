<?php

namespace KozakGroup\RestErrorLog\Plugin\Webapi\Controller\Rest;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\SynchronousRequestProcessor as OriginalSynchronousRequestProcessor;
use Psr\Log\LoggerInterface;

class SynchronousRequestProcessorPlugin
{

    /**
     * @param LoggerInterface $logger
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RemoteAddress $remoteAddress,
    ) {
    }

    /**
     * Plugon on webapi processor to catch exceptions and log their details
     *
     * @param OriginalSynchronousRequestProcessor $subject
     * @param callable $proceed
     * @param Request $request
     *
     * @return mixed
     * @throws AuthorizationException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws WebapiException
     */
    public function aroundProcess(
        OriginalSynchronousRequestProcessor $subject,
        callable $proceed,
        Request $request
    ) {

        try {
            $result = $proceed($request);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($this->getLogMessage($e, $request));
            throw new NoSuchEntityException(__($e->getMessage()));
        } catch (AuthorizationException $e) {
            throw new AuthorizationException(__($e->getMessage()));
        } catch (WebapiException $e) {
            $this->logger->error($this->getLogMessage($e, $request));
            throw new WebapiException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            $this->logger->error($this->getLogMessage($e, $request));
            throw new LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error($this->getLogMessage($e, $request));
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    /**
     * Generate message with request details and exception
     *
     * @param \Exception $e
     * @param Request $request
     *
     * @return string
     */
    private function getLogMessage(\Exception $e, Request $request): string
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        return 'IP: ' . $ip
            . ', Method: ' .$request->getMethod()
            . ', Uri: ' . $request->getUriString() . PHP_EOL
            . 'Content: ' . $request->getContent() . PHP_EOL
            . 'Message: ' . $e->getMessage() . PHP_EOL
            . 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
    }
}
