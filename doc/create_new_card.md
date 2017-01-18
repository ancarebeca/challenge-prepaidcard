## Create a new card:

URL:
    `POST` /cards 

Success Response:

    - Code: `200`
    
    - Content: 
        ```json
            {
              "id": "bb8b6a83-e06d-4da6-96be-6986dfb8e71d",
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
            Error: No route found for "POST /cards".
        ```
