
# Magento2 AI ChromaDb Client
This module is a base module for further development of AI related module. This module provides the methods to easily configure and access the ChromaDb and our LLm server.

## ChromaDb
Chroma is a database for building AI applications with embeddings. It comes with everything you need to get started built in, and runs on your machine.

Reference links:-
1. https://docs.trychroma.com/usage-guide
2. https://docs.trychroma.com/api-reference

We have developed the following adapter class to access the Chroma database and perform the necessary operations.

Here is a list of the available adapters for your application :-

- createCollection 
    
    This method create a new Collection in Chroma.
        
        - Params: 
            string $collectionName [required]
            array $metaData [optional]
            bool $getOrCreate [optional]
            [false = create's if not present, true = also provides details]
        - Returns:
            {
                "name": "test",
                "id": "xxxx-xxxx-xxxx-xxx-xxxxxxxxxxxx",
                "metadata": null,
                "tenant": "default_tenant",
                "database": "default_database"
            }

- getCollection 
    
    This method get a Collection in Chroma by Name.
        
        - Params: 
            string $collectionName [required]
        - Returns:
            {
                "name": "test",
                "id": "xxxx-xxxx-xxxx-xxx-xxxxxxxxxxxx",
                "metadata": null,
                "tenant": "default_tenant",
                "database": "default_database"
            }

- deleteCollection 
    
    This method delete a Collection in Chroma by Name.
        
        - Params: 
            string $collectionName [required]
        - Returns: null on success else Error

- countCollectionItems
    
    This method get's the count of the collection by collectionId.
        
        - Params:
            string $collectionId [required]
        - Returns: (int) ItemCount on success else Error

- addCollectionItems
    
    This method add's Data To Chroma Collection.
        
        - Params:
            string $collectionId [required]
            array $requestBody 
            $requestBody = [ 
                'ids' => [""], [required]
                'embeddings' => '',
                'metadatas' => '',
                'documents' => ''
            ]
        - Returns: 
            Success:- (bool) true            

- getCollectionItems
    
    This method get's Data To Chroma Collection.
        
        - Params:
            string $collectionId [required]
            array $requestBody 
            $requestBody = [
                'ids' => [""],
                'where' => '',
                'where_document' => ''
            ]
        - Returns: 
            Success:- 
                {
                    "ids": ["1"],
                    "embeddings": null,
                    "metadatas": [{"name": "developer"}],
                    "documents": ["doc1"],
                    "uris": null,
                    "data": null
                }

- updateCollectionItems
    
    This method update's Data To Chroma Collection.
        
        - Params:
            string $collectionId [required]
            array $requestBody 
            $requestBody = [ 
                'ids' => [""], [required]
                'embeddings' => '',
                'metadatas' => '',
                'documents' => ''
            ]
        - Returns: 
            Success:- null

- upsertCollectionItems
    
    This method upsert's Data To Chroma Collection, which updates existing items, or adds them if they don't yet exist.
        
        - Params:
            string $collectionId [required]
            array $requestBody 
            $requestBody = [ 
                'ids' => [""], [required]
                'embeddings' => '',
                'metadatas' => '',
                'documents' => ''
            ]
        - Returns: 
            Success:- null

- deleteCollectionItems
    
    This method delete's Data To Chroma Collection.
        
        - Params:
            string $collectionId [required]
            array $requestBody 
            $requestBody = [
                'ids' => [''],
                'where' => '',
                'where_document' => ''
            ]
        - Returns: 
            Success:- [
                "2" [deleted id]
            ]


## LLM
We have developed the following adapter class to access the LLM server to perform the necessary operations.

- createTextEmbeddings

    This method is used to provide text and create embedding for the same.

        - Params:
            string $text [required]
        - Returns:
            Success:- 
                {
                    "embeddings": [
                        0.02312278561294079,
                        -0.06400344520807266,
                        0.016843223944306374,
                        -0.03972741961479187,
                        -0.005269589833915234,
                        ....763 times
                    ]
                }

- createImageEmbeddings

    This method is used to provide image in format of base64 and create embedding for the same.

        - Params:
            string $imageBase64 [required]
        - Returns:
            Success:- 
                {
                    "embeddings": [
                        0.02312278561294079,
                        -0.06400344520807266,
                        0.016843223944306374,
                        -0.03972741961479187,
                        -0.005269589833915234,
                        ....763 times
                    ]
                }

- textQueryEmbeddings

    This method is used to provide embedding of the text along with the extract data in form of category.

        - Params:
            string $text [required]
        - Returns:
            Success:- 
                {
                    "embeddings": [
                        0.02312278561294079,
                        -0.06400344520807266,
                        0.016843223944306374,
                        -0.03972741961479187,
                        -0.005269589833915234,
                        ....763 times
                    ],
                    "extract_data": {
                        "AGE_CATEGORY": [
                            "women",
                            "girls"
                        ]
                    }
                }