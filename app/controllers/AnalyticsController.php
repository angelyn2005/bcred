<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AnalyticsController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Require admin only
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            redirect('login');
            exit;
        }

        // Load the Request model para may real data ka dito
        $this->call->model('RequestModel');
    }

    public function index()
    {
        $monthsToShow = 12;
        $documentTypes = [
            'Barangay Clearance',
            'Indigency Certificate',
            'Residency Certificate',
            'Business Permit',
            'Barangay ID',
        ];

        $totalRequests   = $this->RequestModel->countAll();
        $approvedRequests = $this->RequestModel->countByStatus('approved');
        $pendingRequests  = $this->RequestModel->countByStatus('pending');

        $monthlyRaw = $this->RequestModel->getMonthlyRequestCounts($monthsToShow);
        [$monthlyLabels, $monthlyValues] = $this->buildMonthlySeries($monthsToShow, $monthlyRaw);

        $docRows = $this->RequestModel->getDocumentDistributionRaw();
        $docDistribution = [];
        if (!empty($docRows)) {
            foreach ($docRows as $row) {
                $type = $row['document_type'] ?? 'Unspecified';
                if ($type === '' || $type === null) {
                    $type = 'Unspecified';
                }
                $docDistribution[$type] = (int) ($row['total'] ?? 0);
            }
        } else {
            $docDistribution = array_fill_keys($documentTypes, 0);
        }

        $data = [
            'total_requests'    => $totalRequests,
            'approved_requests' => $approvedRequests,
            'pending_requests'  => $pendingRequests,
            'monthly_labels'    => $monthlyLabels,
            'monthly_values'    => $monthlyValues,
            'doc_labels'        => array_keys($docDistribution),
            'doc_values'        => array_values($docDistribution),
        ];

        $this->call->view('admin/analytics', $data);
    }

    private function buildMonthlySeries(int $months, array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            if (!empty($row['ym'])) {
                $map[$row['ym']] = (int) ($row['total'] ?? 0);
            }
        }

        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $timestamp = strtotime(sprintf('-%d months', $i));
            $key = date('Y-m', $timestamp);
            $labels[] = date('M', $timestamp);
            $values[] = $map[$key] ?? 0;
        }

        return [$labels, $values];
    }
}
