## Make a reverse

URL:
 `GET` /cards/{cardId}/statements 

Success Response:

    - Code: `200`
    - Content: 
    
    ```json
       [
        {
          "card_id": "356127dd-e0a7-4882-9b68-45ed83725f0a",
          "id": "821fe144-126e-42a2-8d07-c572cc20c960",
          "amount": {
            "amount": "0",
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
    ```
Error Response:

    - Code: `400`
    - Content: 
        ```json
        ```
        
    - Code: `404`
    - Content: 
        ```json
        ```