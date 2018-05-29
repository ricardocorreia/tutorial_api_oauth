## How to set a Symfony based REST API, with OAuth as authorization protocol

### So, you want to set a Symfony based REST API… Allow us to give you a few tips on how to proceed.

`Nov 10th 2017`

![Setting a Symfony based REST API with OAuth](img/banner.jpg)

SensioLabs, the creators of [Symfony](https://symfony.com/), describe it as “a set of PHP Components, a Web Application framework, a Philosophy, and a Community — all working together in harmony. It´s only natural you´re eager to get started with it! This tutorial will show you how to set a Symfony based REST API using OAuth as authorization protocol.

## First things first

You will need to install a Symfony environment. The [official documentation](https://symfony.com/doc/current/setup.html) guides on this process and explains how to solve common issues.

We used the latest stable version at the time we were writing, which is Symfony 3.3.10. Also, you may want to set an [Apache](https://www.apache.org/) or [Nginx](https://www.nginx.com/) VirtualHost, so you can access your app from your web server. The best way to do this is following the [official docs](https://symfony.com/doc/current/setup/web_server_configuration.html).

And try to access your app by calling your host in the browser:

![Calling your host in the browser](img/screen1.png)

So far so good...

### Let's deal with some requirements

Next thing we'll need is FOSRestBundle to handle the REST requests in our application, and again you should install it as described in the [official docs](https://symfony.com/doc/master/bundles/FOSRestBundle/1-setting_up_the_bundle.html).

```bash
$ composer require friendsofsymfony/rest-bundle
```

And then add the bundle to your `app/AppKernel.php`:

```PHP
<?php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new FOS\RestBundle\FOSRestBundle(),
        ];

        // ...
    }
}
```

Since well need to deal with content serialization and deserialization, you will also need to use [JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle).

Next thing we will do is creating a bundle where we can set our REST controller and our routes, and let's call it `MySuperRestBundle`:

```bash
$ php bin/console generate:bundle --namespace=cleverti/MySuperRestBundle --no-interaction
```

### Now it's time for the controller

Now that our bundle is created we will need a controller as well:

```bash
$ php bin/console generate:controller --no-interaction --controller=cleverti\MySuperRestBundle:Rest
```

This will create the controller class in `src/cleverti/MySuperRestBundle/Controller/RestController.php`, which is where we will focus right now.

The `generate:controller` command will only generate a base skeleton of your controller class, which means that you will need to add all the mechanics for your controller to respond to the routes you want to define, and this is done by using Actions.

By default, our controller looks like this:

```PHP
<?php

namespace cleverti\MySuperRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RestController extends Controller
{
}
```

... which means that we will need to make a few changes. After all, this is just the initial skeleton.

So, let's start by defining a route for our controller.

You can set a prefix for your controllers, which is pretty useful if you want to have plenty of routes under the same controller, and several controllers under the same bundle. For that you should add the following in `app/config/routing.yml`:

```YAML
cleverti_my_super_rest_bundle:
    resource: "@clevertiMySuperRestBundle/Resources/config/routing.yml"
    prefix:   /api
```

Now we are ready to define our routes in our controller. Because we will use REST, our controller will not extend Symfony's default controller, but `FOSRestController` instead.

So, let's define a test action for us to see the API magic at work, and we will call it `restGetAction` in this case. We will also use an annotation to set our route, like this one: `@Get("/get/cleverti")`.

This route will define both your method, and path to call. Since this controller is set to have the prefix `/api` you will need to call it by: `GET /api/get/cleverti`. We should get something like this in our controller:

```PHP
<?php

namespace cleverti\MySuperRestBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;

class RestController extends FOSRestController
{
    /**
     * Here goes our route
     * @Get("/get/cleverti")
     */
    public function restGetAction(Request $request)
    {
        // Do something with your Request object
        $data = array(
            "name" => "cleverti",
            "extra" => "Is awesome!"
        );
        $view = $this->view($data, 200);
        return $this->handleView($view);
    }
}
```

And we are almost ready.

We just need to set some basic settings in to make FosRestBundle to listen for REST calls, and since we will use its view engine we won't need to set any twig template for our responses. So, add the following to your `app/config/config.yml`:

```YAML
fos_rest:
    routing_loader:
        default_format: json
    view:
        view_response_listener: true
```

And now go ahead, and call your route with your favorite REST client, or since the method defined for this route is GET you can also call if from your web browser. We used [Insomnia](https://insomnia.rest/) as REST client in our example.

![Calling our route](img/screen2.png)

And success!

Out API listens to your request, and answers with a JSON response, exactly as it was defined. And now you ask me: What about access control? I don't want most of my API methods to be publicly available...

### Setting up OAuth

There are several authorization methods and one of the most used is OAuth 2.0. It allows you to use authentication with an external provider, which is kind of cool if you intend to use Twitter, Facebook or other provider for your users to identify themselves. In our example we will use `FOSUserBundle` as user provider, so, let's create two bundles, one for the `FOSUserBundle` entity, and another bundle for the `FOSOAuthServerBundle` entities that we need to set during the installation process:

```bash
$ php bin/console generate:bundle --namespace=cleverti/UserBundle --no-interaction
$ php bin/console generate:bundle --namespace=cleverti/OAuthBundle --no-interaction
```

Then, you should install `FOSUserBundle` as explained in the [official documentation](https://symfony.com/doc/master/bundles/FOSUserBundle/index.html).

And then install `FOSOAuthServerBundle` as shown [here](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Resources/doc/index.md).

Also, don't forget to make `/api` available for authenticated users only, by adding it in the `access_control` block, as follows:

```YAML
security:
    ...
    access_control:
        - { path: ^/api, roles: [ IS_AUTHENTICATED_FULLY ] }
```

The path section will be the path of your routes, partial or absolute, that you want to protect. And the roles part is where you define the roles that your users must have, in order to being able to access the routes. Since this is just an example I will not focus on the roles, I am just defining that any authenticated user may have access, but not an anonymous user. You can know more about the roles here. This is how my `app/config/config.yml` looks like:

<details>
 <summary><strong>app/config/config.yml</strong> (click to expand)</summary>

```YAML
imports:
    - { resource: parameters.yml }
    - { resource: security.yml }
    - { resource: services.yml }
    - { resource: "@clevertiMySuperRestBundle/Resources/config/services.yml" }
    - { resource: "@clevertiOAuthBundle/Resources/config/services.yml" }
    - { resource: "@clevertiUserBundle/Resources/config/services.yml" }

parameters:
    locale: en

framework:
    #esi: ~
    #translator: { fallbacks: ['%locale%'] }
    secret: '%secret%'
    router:
        resource: '%kernel.project_dir%/app/config/routing.yml'
        strict_requirements: ~
    form: ~
    csrf_protection: ~
    validation: { enable_annotations: true }
    #serializer: { enable_annotations: true }
    templating:
        engines: ['twig']
    default_locale: '%locale%'
    trusted_hosts: ~
    session:
        # https://symfony.com/doc/current/reference/configuration/framework.html#handler-id
        handler_id: session.handler.native_file
        save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'
    fragments: ~
    http_method_override: true
    assets: ~
    php_errors:
        log: true

# Twig Configuration
twig:
    debug: '%kernel.debug%'
    strict_variables: '%kernel.debug%'

# Doctrine Configuration
doctrine:
    dbal:
        driver: pdo_mysql
        host: '%database_host%'
        port: '%database_port%'
        dbname: '%database_name%'
        user: '%database_user%'
        password: '%database_password%'
        charset: UTF8
    orm:
        auto_generate_proxy_classes: '%kernel.debug%'
        naming_strategy: doctrine.orm.naming_strategy.underscore
        auto_mapping: true

# Swiftmailer Configuration
swiftmailer:
    transport: '%mailer_transport%'
    host: '%mailer_host%'
    username: '%mailer_user%'
    password: '%mailer_password%'
    spool: { type: memory }

# FosRestBundle Configuration
fos_rest:
    routing_loader:
        default_format: json
    view:
        view_response_listener: true

# FOSUserBundle Configuration
fos_user:
    db_driver: orm
    firewall_name: main
    user_class: cleverti\UserBundle\Entity\User
    from_email:
        address: "%mailer_user%"
        sender_name: "%mailer_user%"

# FOSOAuthServerBundle Configuration
fos_oauth_server:
    db_driver: orm
    client_class:        cleverti\OAuthBundle\Entity\Client
    access_token_class:  cleverti\OAuthBundle\Entity\AccessToken
    refresh_token_class: cleverti\OAuthBundle\Entity\RefreshToken
    auth_code_class:     cleverti\OAuthBundle\Entity\AuthCode
    service:
        user_provider: fos_user.user_provider.username
        options:
            access_token_lifetime: 86400
            refresh_token_lifetime: 1209600
            auth_code_lifetime: 30
```
</details>

And this is my `app/config/security.yml`:

<details>
 <summary><strong>app/config/security.yml</strong> (click to expand)</summary>

```YAML
security:
    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt
        
    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN
    providers:
        in_memory:
            memory: ~

        fos_userbundle:
            id: fos_user.user_provider.username
            
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
            
        oauth_token:
            pattern:    ^/oauth/v2/token
            security:   false
        oauth_authorize:
            pattern:    ^/oauth/v2/auth
            form_login:
                provider: fos_userbundle
                check_path: /oauth/v2/auth_login_check
                login_path: /oauth/v2/auth_login
                use_referer: true
        api:
            pattern:    ^/api
            fos_oauth:  true
            stateless:  true
            anonymous:  false
        main:
            pattern: ^/
            form_login:
                provider: fos_userbundle
                csrf_token_generator: security.csrf.token_manager
            
            anonymous: true
    access_control:
        - { path: ^/api, roles: [ IS_AUTHENTICATED_FULLY ] }
```
</details>

From this point on, if you try to make another call to your route you should no longer have access since you are not authenticated yet. This is what you should get:

![Testing authentication](img/screen3.png)

When we're done with the installation of `FOSUserBundle` and `FOSOAuthServerBundle` we'll need to create an OAuth client and a User, so we can create Oauth Access Tokens. We'll need that for our request.

So, create the following command at `src/cleverti/OAuthBundle/Command/ClientCreateCommand.php`:

```PHP
<?php
namespace cleverti\OAuthBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class ClientCreateCommand extends ContainerAwareCommand
{
    protected function configure ()
    {
        $this
            ->setName('oauth:client:create')
            ->setDescription('Creates a new client')
            ->addOption('redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets the redirect uri. Use multiple times to set multiple uris.', null)
            ->addOption('grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Set allowed grant type. Use multiple times to set multiple grant types', null)
        ;
    }
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $clientManager->updateClient($client);
        $output->writeln("Added a new client with  public id <info>".$client->getPublicId()."</info> and secret <info>".$client->getSecret()."</info>");
    }
}
```

And create an OAuth client by running the command you just created with two options - the redirect URIs you desire to use, and the grant types you want to allow this client to use, which should give you an output like the following:

```bash
$ php bin/console oauth:client:create --redirect-uri=http://www.cleverti.com --grant-type=password --grant-type=refresh_token
Added a new client with  public id 1_4654cpi0zu0wokk4g04gk0w444wkkwcs4sg0okoo0gks0gcokg and secret 1h7xdjjo6ixwwow0w00oss4sgc0w8o48ocgw808w0gg4s40owc
```

And you can use the default command provided by FOSUserBundle to create a user:

```bash
$ php bin/console fos:user:create ricardo.correia ricardo.correia@cleverti.com password
```

Now let's try to request an Access Token, by making a POST request to the OAuth route `/oauth/v2/token`. This route expects `grant_type` with value password, the `client_id` and `client_secret`, and the `username` and `password` of the user:

```JSON
{
    "grant_type": "password",
    "client_id": "1_4654cpi0zu0wokk4g04gk0w444wkkwcs4sg0okoo0gks0gcokg",
    "client_secret": "1h7xdjjo6ixwwow0w00oss4sgc0w8o48ocgw808w0gg4s40owc",
    "username": "ricardo.correia",
    "password": "password"
}
```

If we make a request with our REST client, we get the following result:

![Getting an OAuth token](img/screen4.png)

Ok, everything is going great so far, and we got our access token.

Now we'll need to use our access token in all our requests, by setting an Authorization header, as follows:

```
Authorization: Bearer MTI4ZmUyNTg1ZDgwM2Y0ZmJlZjg3OWNlNTA2ZDE5ZTk4ZTQzZGMzNjllOGE4YWI5Yzc0ZWQxMWQ1MjVjNmY5MA
```

So, let's try to access our protected route `/api/get/cleverti`, but this time using the `Authorization` header:

![Getting authenticated](img/screen5.png)

Success! Our route is protected and available to authenticated users only, using OAuth 2.0. And it's pretty much it.

Also, you may want to refresh the access token when its lifetime expires, and this is done using the `/oauth/v2/token` route as well, but this time you will need to send the `grant_type` as `refresh_token`, `client_id` and `client_secret`, and the `refresh_token` instead of user and password:

```JSON
{
    "grant_type": "refresh_token",
    "client_id": "1_4654cpi0zu0wokk4g04gk0w444wkkwcs4sg0okoo0gks0gcokg",
    "client_secret": "1h7xdjjo6ixwwow0w00oss4sgc0w8o48ocgw808w0gg4s40owc",
    "refresh_token": "OWQyNTI1MDk4OTlhZTk0NDdlYzA0YTM4ZTFlZDZjMmQxNDY3OTBjMzBiNTYzOWMwMTgxN2UwMGYwYWE3M2RiYw"
}
```

And that call will return a new `access_token` and a new `refresh_token`:

![Refreshing the access token](img/screen6.png)

And that's all for now. You're good to go!

Make sure you check this repository for the full source.

We hope you enjoy your coding as much as we do. Have fun!


#### Written by Ricardo Correia | Developer at Cleverti