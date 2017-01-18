##  Make a request authorization:

URL:
 `POST` /cards/{cardId}/request-authorization   

Body:

    ```json
         {  
            "amount":"123",
            "description":"Costa"
         }
    ```
    
Success Response:

    - Code: `200`
    
    - Content: 
    
        ```json
        {
          "card_id": "3fd06f71-e837-44d1-994e-9b0bc30cc380",
          "id": "22b17ba2-d696-4430-9cb9-d8a6bdce0a24",
          "amount": {
            "amount": "50",
            "currency": "GBP"
          },
          "description": "Costa",
          "captured_amount": {
            "amount": "0",
            "currency": "GBP"
          },
          "reversed_amount": {
            "amount": "0",
            "currency": "GBP"
          }
        }
        ```
Error Response:

    - Code: `400`
    - Content: 
        ```json
             {
                "Error": "You current balance is not enough to complete this transaction."
              }
        ```
        
    - Code: `404`
    - Content: 
        ```json
            {
              "error": "Card d1df59c6-48a4-482d-89bb-0d96d906c4755 not found."
             }
        ```
        
    - Code: `400`
    - Content:  
       ```json
            {
             "error": "amount parameter is missing"
            }
        ```
