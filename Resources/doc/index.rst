Here are the necessary parts of the security.yml:

    providers:
        apisecurity:
            id:        jhb_hmac.user_provider

        api_secured_area:
            provider:   apisecurity
            pattern:    ^/(.*)?
            apisecurity: true

and of config.yml:

jhb_hmac:
    users:
        apiuser:
            secretKey: VERYSECRET
            publicKey: OPEN
            roles: [ ROLE_API_AUTH ]