<?php
namespace Oro\Bundle\TranslationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class Controller
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(protected TranslatorInterface $translator, protected Environment $templating, protected string $template, protected $options)
    {
        if (empty($template)) {
            throw new \InvalidArgumentException('Please provide valid twig template as third argument');
        }
    }

    /**
     * Action point for js translation resource
     *
     * @param string $_locale
     * @return Response
     */
    public function indexAction(Request $request, $_locale)
    {
        $domains = $this->options['domains'] ?? [];
        $debug = isset($this->options['debug']) ? (bool)$this->options['debug'] : false;

        $content = $this->renderJsTranslationContent($domains, $_locale, $debug);

        return new Response($content, 200, ['Content-Type' => $request->getMimeType('js')]);
    }

    /**
     * Combines JSON with js translation and renders js-resource
     *
     * @param string $locale
     * @param bool $debug
     * @return string
     */
    public function renderJsTranslationContent(array $domains, $locale, $debug = false)
    {
        $domainsTranslations = $this->translator->getTranslations($domains, $locale);

        $result = [
            'locale'         => $locale,
            'defaultDomains' => $domains,
            'messages'       => [],
        ];
        if ($debug) {
            $result['debug'] = true;
        }

        foreach ($domainsTranslations as $domain => $translations) {
            $result['messages'] += array_combine(
                array_map(
                    fn ($id) => sprintf('%s:%s', $domain, $id),
                    array_keys($translations)
                ),
                array_values($translations)
            );
        }

        return $this->templating->render($this->template, ['json' => $result]);
    }
}
