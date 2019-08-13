<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\ErrorHandler\src;

use const Chevere\CLI;
use DateTime;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse as HttpJsonResponse;
use JakubOnderka\PhpConsoleColor\ConsoleColor;
use Chevere\Console\Console;
use Chevere\ErrorHandler\ErrorHandler;
use Chevere\Json;

/**
 * Provides ErrorHandler output by passing a Formatter. FIXME: Don't handle responses!
 */
final class Output
{
    /** @var string The console|html content representation */
    private $content;

    /** @var string The text/plain content representation */
    private $textPlain;

    /** @var array */
    private $templateTags;

    /** @var ErrorHandler */
    private $errorHandler;

    /** @var Formatter */
    private $formatter;

    /** @var string */
    private $output;

    /** @var array */
    private $headers = [];

    /** @var string The rich template string. Note: Placeholders won't be visible when dumping to console */
    private $richTemplate;

    /** @var string The plain template string. */
    private $plainTemplate;

    public function __construct(ErrorHandler $errorHandler, Formatter $formatter)
    {
        $this->errorHandler = $errorHandler;
        $this->formatter = $formatter;
        $this->generateTemplates();
        $this->parseTemplates();
        if ($errorHandler->request()->isXmlHttpRequest()) {
            $this->setJsonOutput();
        } else {
            if (CLI) {
                $this->setConsoleOutput();
            } else {
                $this->setHtmlOutput();
            }
        }
    }

    public function textPlain(): string
    {
        return $this->textPlain;
    }

    public function out(): void
    {
        if ($this->errorHandler->request()->isXmlHttpRequest()) {
            $response = new HttpJsonResponse();
        } else {
            $response = new HttpResponse();
        }
        $response->setContent($this->output);
        $response->setLastModified(new DateTime());
        $response->setStatusCode(500);
        foreach ($this->headers as $k => $v) {
            $response->headers->set($k, $v);
        }
        $response->send();
    }

    private function parseTemplates(): void
    {
        $this->templateTags = $this->formatter->getTemplateTags();
        $this->content = strtr($this->richTemplate, $this->templateTags);
        $this->addTemplateTag('content', $this->content);
        $this->textPlain = strtr($this->plainTemplate, $this->templateTags);
    }

    /**
     * $table stores the template placeholders and its value.
     *
     * @param string $tagName Template tag name
     * @param mixed  $value   value
     */
    private function addTemplateTag(string $tagName, $value): void
    {
        $this->templateTags["%$tagName%"] = $value;
    }

    /**
     * @param string $tagName Template tag name
     */
    private function getTemplateTag(string $tagName): string
    {
        return $this->templateTags["%$tagName%"];
    }

    private function setJsonOutput(): void
    {
        $json = new Json();
        $this->headers = array_merge($this->headers, Json::CONTENT_TYPE);
        $response = [Template::NO_DEBUG_TITLE_PLAIN, 500];
        $log = [
            'id' => $this->getTemplateTag('id'),
            'level' => $this->formatter->dataKey('loggerLevel'),
            'filename' => $this->getTemplateTag('logFilename'),
        ];
        switch ($this->errorHandler->isDebugEnabled()) {
            case 0:
                unset($log['filename']);
                break;
            case 1:
                $response[0] = $this->formatter->dataKey('thrown').' in '.$this->getTemplateTag('file').':'.$this->getTemplateTag('line');
                $error = [];
                foreach (['file', 'line', 'code', 'message', 'class'] as $v) {
                    $error[$v] = $this->getTemplateTag($v);
                }
                $json->data->setKey('error', $error);
                break;
        }
        $json->data->setKey('log', $log);
        $json->setResponse(...$response);
        $this->output = (string) $json;
    }

    private function setHtmlOutput(): void
    {
        if ($this->errorHandler->isDebugEnabled()) {
            $bodyTemplate = Template::DEBUG_BODY_HTML;
        } else {
            $this->content = Template::NO_DEBUG_CONTENT_HTML;
            $this->addTemplateTag('content', $this->content);
            $this->addTemplateTag('title', Template::NO_DEBUG_TITLE_PLAIN);
            $bodyTemplate = Template::NO_DEBUG_BODY_HTML;
        }
        $this->addTemplateTag('body', strtr($bodyTemplate, $this->templateTags));
        $this->output = strtr(Template::HTML_TEMPLATE, $this->templateTags);
    }

    private function setConsoleOutput(): void
    {
        foreach ($this->formatter->consoleSections() as $k => $v) {
            if ('title' == $k) {
                $tpl = $v[0];
            } else {
                Console::cli()->out->section(strtr($v[0], $this->templateTags));
                $tpl = $v[1];
            }
            $message = strtr($tpl, $this->templateTags);
            if ('title' == $k) {
                Console::cli()->out->error($message);
            } else {
                $message = preg_replace_callback('#<code>(.*?)<\/code>#', function ($matches) {
                    $consoleColor = new ConsoleColor();

                    return $consoleColor->apply('light_blue', $matches[1]);
                }, $message);
                Console::cli()->out->writeln($message);
            }
        }
        Console::cli()->out->writeln('');
    }

    private function generateTemplates(): void
    {
        $templateStrings = new TemplateStrings($this->formatter);
        $this->richTemplate = $templateStrings->rich();
        $this->plainTemplate = $templateStrings->plain();
    }
}
