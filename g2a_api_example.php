<?php

require __DIR__ . '/vendor/autoload.php';

use G2A\IntegrationApi\Model\Config;
use G2A\IntegrationApi\Client;
use G2A\IntegrationApi\Request\ProductsListRequest;

// Configure o cliente da API
$config = new Config(
    'taironest@outlook.com',         // Insira seu e-mail
    'sandboxapi.g2a.com',       // URL do sandbox
    'ibHtsEljmCxjOFAn',              // Insira sua hash API
    'HrsPmuOlWjqBMHnQWIgfchUqBTBYcRph'              // Insira sua chave API
);

$g2aApiClient = new Client($config);

// Obter a lista de produtos
$request = new ProductsListRequest($g2aApiClient);
$request->setPage(1)->setMinQty(5);
$request->call();

$response = $request->getResponse();

foreach ($response->getProducts() as $product) {
    echo $product->getId() . ' - ' . $product->getName() . PHP_EOL;
}

