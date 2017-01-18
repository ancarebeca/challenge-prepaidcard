## TopUp a card


URL:
 `PATCH` /cards/{cardId}    

Body:

    ```json
         {  
            "amount":"123",
         }
    ```
    
Success Response:

    - Code: `200`
    
    - Content: 
        ```json
        {
          "id": "3fd06f71-e837-44d1-994e-9b0bc30cc380",
          "balance": {
            "amount": "300",
            "currency": "GBP"
          },
          "blocked_amount": {
            "amount": "0",
            "currency": "GBP"
          },
          "transactions": [
            {
              "card_id": "3fd06f71-e837-44d1-994e-9b0bc30cc380",
              "id": "e2d7ea2d-e043-414d-8ead-295a063be17d",
              "amount": {
                "amount": "300",
                "currency": "GBP"
              },
              "description": "Top-up",
              "captured_amount": {
                "amount": "0",
                "currency": "GBP"
              },
              "reversed_amount": {
                "amount": "0",
                "currency": "GBP"
              }
            }
          ]
        }
        ```
    
Error Response:

    - Code: `404`
    
    - Content: 
        ```json
            Error: Card [f97f8091-3bf6-4bfc-b9f0-be03f2283382] not found.
        ```