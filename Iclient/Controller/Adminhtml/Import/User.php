<?php

namespace Expertime\Iclient\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Expertime\Iclient\Model\ResourceModel\User\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class User extends \Magento\Backend\App\Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $customerRepository;

    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context,
                                Filter $filter,
                                CollectionFactory $collectionFactory,
                                StoreManagerInterface $storeManager,
                                CustomerInterfaceFactory $customerFactory,
                                CustomerRepositoryInterface $customerRepository
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory  = $customerFactory;
        $this->storeManager     = $storeManager;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
        $storeId = $this->storeManager->getStore()->getId();

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {

            if(!$item->getData('imported')) {

                $customer = $this->customerFactory->create();
                $path = $this->uploadAvatar($customer, $item->getData('avatar'));
                $email = time() . '@something.com';
                $hashedPassword = $this->_objectManager->get('\Magento\Framework\Encryption\EncryptorInterface')->getHash('password', true);
                $customer->setStoreId($storeId)
                    ->setFirstname($item->getData('first_name'))
                    ->setLastname($item->getData('last_name'))
                    ->setWebsiteId($websiteId)
                    ->setCustomAttribute('profile_picture', $path)
                    ->setEmail($email);
                $this->customerRepository->save($customer, $hashedPassword);

                $customer = $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
                $customer->setWebsiteId($websiteId)->loadByEmail($email);

                $item->setData('imported', 1);
                $item->save();
            }
            sleep(1);
        }

        $this->messageManager->addSuccess(__('A total of %1 customer(s) have been imported.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    public function uploadAvatar($customer, $avatar)
    {
        $avatar = str_replace('https', 'http', $avatar);
        set_time_limit(0);
        $file = basename($avatar);
        $exploded = explode('.', $file);
        $name = uniqid() . '.' . end($exploded);

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL,$avatar);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($curl_handle, CURLOPT_TIMEOUT,500);
        curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT,TRUE);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handle, CURLOPT_REFERER, "https://s3.amazonaws.com");
        curl_setopt($curl_handle,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $query = curl_exec($curl_handle);
        if (curl_error($curl_handle)) {
             throw new \Exception(curl_error($curl_handle));
        }
        curl_close($curl_handle);

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($name, '/');

        $path = $directory->getAbsolutePath($fileName);

        //file_put_contents($path, $query);

        $fp = fopen($path,'x');
        fwrite($fp, $query);
        fclose($fp);

        if (!$directory->isFile($fileName))
        {
            throw new NotFoundException(__('Page not found.'));
        }
        return $path;
    }
}