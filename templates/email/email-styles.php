<?php
/**
 * Email Template Part: Inline CSS Styles
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return "
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }
    .email-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .email-header {
        background-color: var(--wp--preset--color--primary, #0073aa);
        color: white;
        padding: 20px;
        text-align: center;
    }
    .email-header h1 {
        margin: 0;
        font-size: 24px;
    }
    .email-content {
        padding: 30px;
    }
    .email-content h2 {
        color: var(--wp--preset--color--primary, #0073aa);
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
    }
    .appointment-details {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin: 20px 0;
    }
    .appointment-details ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .appointment-details li {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }
    .appointment-details li:last-child {
        border-bottom: none;
    }
    .custom-fields {
        margin-top: 20px;
    }
    .custom-field {
        margin-bottom: 10px;
    }
    .custom-field strong {
        display: inline-block;
        min-width: 120px;
    }
    .email-footer {
        background-color: #f8f8f8;
        padding: 20px;
        text-align: center;
        font-size: 12px;
        color: #666;
    }
    .email-footer a {
        color: var(--wp--preset--color--primary, #0073aa);
        text-decoration: none;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .status-pending {
        background-color: #ffc107;
        color: #000;
    }
    .status-confirmed {
        background-color: #28a745;
        color: #fff;
    }
    .status-cancelled {
        background-color: #dc3545;
        color: #fff;
    }
    .status-completed {
        background-color: #6c757d;
        color: #fff;
    }
";