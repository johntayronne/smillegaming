// Função para criar um pedido
const createOrder = async (productId, maxPrice) => {
    const response = await fetch('https://api.g2a.com/v1/order', {
        method: 'POST',
        headers: {
            'Authorization': 'nAxqxnnouJiWkTVw,JtXViYBkIBYsxAyKAjAVDOBDoQVUuBQS', // Substitua por seu ClientId e Client Secret
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            max_price: maxPrice
        })
    });
    return await response.json();
};

// Evento de clique para o botão "Buy"
document.querySelector('.buy-btn').addEventListener('click', () => {
    const productId = 'your_product_id'; // Substitua pelo ID do produto que você deseja comprar
    const maxPrice = 100; // Substitua pelo preço máximo que você deseja pagar

    createOrder(productId, maxPrice).then(orderResponse => {
        console.log(orderResponse);
        // Aqui você pode exibir uma mensagem de sucesso ou redirecionar o usuário
        alert('Pedido criado com sucesso! ID do pedido: ' + orderResponse.id);
    }).catch(error => {
        console.error('Erro ao criar o pedido:', error);
        alert('Ocorreu um erro ao criar o pedido.');
    });
});
