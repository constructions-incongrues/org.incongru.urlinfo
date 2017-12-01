<?php
use Embed\Embed;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use \Neomerx\Cors\Analyzer;
use \Psr\Http\Message\RequestInterface;
use \Neomerx\Cors\Contracts\AnalysisResultInterface;
use \Neomerx\Cors\Strategies\Settings as CorsSettings;
use \Neomerx\Cors\Contracts\Constants\CorsResponseHeaders;

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/Kernel.php';

class Kernel extends App\Kernel
{
    use ControllerTrait;

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        // kernel is a service that points to this class
        // optional 3rd argument is the route name
        $routes->add('/', 'kernel:homeAction');
        $routes->add('/info', 'kernel:infoAction');
    }

    public function homeAction(Request $request)
    {
        return $this->render('home.html.twig');
    }

    /**
     * @see https://github.com/oscarotero/Embed
     */
    public function infoAction(Request $request)
    {
        // Extract embedding informations from URL
        $response = Embed::create(
            $request->query->get('url'),
            array_merge(Embed::$default_config, $request->query->get('options', []))
        );
        $defaultFields = [
            'authorName',
            'authorUrl',
            'code',
            'description',
            'feeds',
            'height',
            'imagesUrls',
            'license',
            'providerIconsUrls',
            'providerName',
            'providerUrl',
            'publishedTime',
            'tags',
            'title',
            'type',
            'url',
            'width',
        ];
        $fields = explode(',', $request->query->get('fields', implode(',', $defaultFields)));
        $informations = [];
        array_walk($fields, function ($field) use (&$informations, $response) {
            $informations[$field] = call_user_func([$response, 'get'.$field]);
        });

        // Add provider data
        foreach ($response->getProviders() as $provider) {
            $providerName = strtolower($provider->getProviderName());
            if (in_array($providerName, explode(',', $request->query->get('providers', '')), true)) {
                $informations[strtolower($provider->getProviderName())] = $provider->getBag()->getAll();
            }
        }

        // Cache response
        $cache = $this->getContainer()->get('cache');
        $cacheKey = hash('sha256', $request->query->get('url'));
        if (!$cache->has($cacheKey)) {
            $response = new JsonResponse(
                $informations,
                200,
                ['Access-Control-Allow-Origin' => '*', 'Access-Control-Request-Method' => 'GET']
            );
            $cache->set($cacheKey, $response);
        }

        return $cache->get($cacheKey);
    }
}

$kernel = new \Kernel('prod', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
