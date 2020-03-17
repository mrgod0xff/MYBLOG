# MYBLOG
Développer avec symfony api platform API pour  un blog.      
Fonctionnalités : 
* Authentification (inscription d'utilisateur, connexion, y compris la confirmation de compte par e-mail);
* Gestion des autorisations (rôles d'utilisateur, privilèges, restriction d'accès); 
* Téléchargements de fichiers via l'API REST;  
* Tests unitaires (PHPUnit) et Tests fonctionnels (Behat)...

## EasyAdmin
![Image de la page d'accueil](https://nsa40.casimages.com/img/2020/03/16/200316115038760405.png)



## JWT Authentification

![LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md)


#### Installation du package via composer:
``` bash
$ composer require lexik/jwt-authentication-bundle
```

#### Générez les clés SSH:

``` bash
$ mkdir -p config/jwt
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

#### Configuration

Configurez le chemin des clés SSH dans config/packages/lexik_jwt_authentication.yaml :

``` yaml
  lexik_jwt_authentication:
      # requis pour la création de jetons
      secret_key:       '%kernel.project_dir%/config/jwt/private.pem' 
      # requis pour la vérification des jetons
      public_key:       '%kernel.project_dir%/config/jwt/public.pem'  
       # pour la création de jetons, l'utilisation d'une variable d'environnement est recommandée
      pass_phrase:      'your_secret_passphrase'
      token_ttl:        3600
```

Configurez le config/packages/security.yaml :

``` yaml
  security:
   ...
    providers:
    #    users_in_memory: { memory: null }
         database:
             entity:
                 class: App\Entity\User
                 property: username
    firewalls:
        ...
        api:
            pattern: ^/api
            stateless: true
            anonymous: true
            provider: users_in_memory
            json_login:
                # here authentication will happen (token generation)
                check_path: /api/login_check
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure
            guard:
                authenticators:
                    - lexik_jwt_authentication.jwt_token_authenticator

            # activate different ways to authenticate
            # https://symfony.com/doc/current/security.html#firewalls-authentication

            # https://symfony.com/doc/current/security/impersonating_user.html
            # switch_user: true

    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        # - { path: ^/admin, roles: ROLE_ADMIN }
        # - { path: ^/profile, roles: ROLE_USER }
         - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
         - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
```
