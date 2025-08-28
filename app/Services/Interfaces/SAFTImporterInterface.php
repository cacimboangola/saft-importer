<?php

namespace App\Services\Interfaces;

interface SAFTImporterInterface
{
    public function loadFile(string $filePath);
    public function importCompany();
    public function importCustomers();
    public function importSales(array $invoiceTypes = []);
    public function importSalesInvoices(array $invoiceTypes = []);
    public function importPurchaseInvoices();
    public function importWorkingDocuments();
    public function importPayments();
}
