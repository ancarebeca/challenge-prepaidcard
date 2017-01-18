## Retrieve a card:

URL:
 `GET` /cards/{cardId} 

Success Response:

    - Code: `200`
    
    - Content: 
        ```json
           {
             "id": "d1df59c6-48a4-482d-89bb-096d906c4755",
             "balance": {
               "amount": "0",
               "currency": "GBP"
             },
             "blocked_amount": {
               "amount": "0",
               "currency": "GBP"
             },
             "transactions": []
           }
        ```
    
Error Response:

    - Code: `404`
    
    - Content: 
    ```json
       {
         "error": "Card [d1df59c6-48a4-482d-89bb-096d906c4755] not found"
       }
    ```
     