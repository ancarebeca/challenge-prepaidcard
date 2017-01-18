prepaid card
===========================

## Requirements
* Symfony v3.1+
* PHP v5.5+
* PHPUnit 
* moneyphp 3.0+
* uuid 3.5+
* jms/serializer-bundle
* docker


## Installation

Installation process: 
```sh
    composer install
```

## Environment setup:

- Install docker
  
    https://docs.docker.com/engine/installation/

- Change host file
```sh
    # /etc/hosts
    192.168.99.100 my-card.dev
```
- Run docker
```sh
    docker-compose up -d 
```
- Base URL: 
```sh
    http://my-card.dev:8080
```

## Run test
```sh
    composer run test
```

## EndPoints
1. [Create a new card](doc/create_new_card.md)
2. [TopUp card](doc/topup_card.md)
3. [Retrieve a card](doc/topup_card.md)
4. [Make a request authorization](doc/make_request_authorization.md)         
5. [Make a transaction reverse](doc/make_reverse.md)         
6. [Make a transaction capture](doc/make_capture.md)     
7. [Make a transaction refund](doc/make_refund.md)
8. [Retrieve statement](doc/statement.md)
         
