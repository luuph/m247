#Installation

Magento2 AIImageSearch module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and AIImageSearch(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/AIImageSearch/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

------------------------------------------------------------------

# API Setup

You can find the code files/instructions(Readme) in ai-model in root directory.

# ChromaDb Setup

You can instructions(Readme) in chromadb-setup in the root directory.

------------------------------------------------------------------

#Additional Commands

1. Create/update existing product images embeddings via terminal
-------------------------------------------
php bin/magento generate:image:embeddings

2. Create/update selected product images embeddings via terminal
---------------------------------------------------
php bin/magento generate:image:embeddings -p 1,2,3

#User Guide

For Magento2 AIImageSearch module's working process follow user guide - https://webkul.com/blog/magento2-ai-image-search-documentation/

#Video Links

Video Tutorial Admin Configuration - 

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
