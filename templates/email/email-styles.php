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
    <style type=\"text/css\">
    /* Reset & Base */
    body, table, td, a {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
    body {
        margin: 0;
        padding: 0;
        width: 100% !important;
        background: #f7fafc;
        font-family: 'Inter', Arial, Helvetica, sans-serif;
        color: #222;
        font-size: 16px;
        line-height: 1.6;
    }
    img {
        border: 0;
        outline: none;
        text-decoration: none;
        max-width: 100%;
        height: auto;
        display: block;
    }
    table {
        border-collapse: collapse !important;
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        width: 100%;
    }
    a {
        color: #0057b8;
        text-decoration: underline;
    }
    p {
        margin: 0 0 1em 0;
    }

    /* Container */
    .email-container {
        max-width: 540px;
        margin: 32px auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(30,41,59,0.10), 0 1.5px 6px rgba(30,41,59,0.06);
        padding: 2.5rem 2rem;
        border: 1px solid #e5e7eb;
    }
    @media only screen and (max-width: 600px) {
        .email-container {
            padding: 1.2rem 0.7rem;
            max-width: 98vw;
        }
    }

    /* Header */
    .email-header {
        text-align: center;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .email-header .email-logo {
        max-width: 80px;
        margin: 0 auto 1rem auto;
        display: block;
    }
    .email-header .email-title {
        font-size: 1.6rem;
        font-weight: 700;
        color: #0057b8;
        margin-bottom: 0.2rem;
        letter-spacing: -0.5px;
    }
    .email-header .email-subtitle {
        color: #64748b;
        font-size: 1.05rem;
        margin-bottom: 0;
    }

    /* Main Content */
    .email-content {
        padding: 2rem 0 1rem 0;
    }
    .email-content h1, .email-content h2, .email-content h3 {
        color: #0057b8;
        font-weight: 700;
        margin-bottom: 1.1rem;
        margin-top: 0;
    }
    .email-content h4, .email-content h5 {
        color: #003366;
        font-weight: 600;
        margin-bottom: 0.7rem;
        margin-top: 0;
    }
    .email-content p {
        color: #222;
        font-size: 1.05rem;
        margin-bottom: 1.1rem;
    }

    /* Appointment Details Table */
    .appointment-details {
        width: 100%;
        background: #f7fafc;
        border-radius: 12px;
        margin: 1.5rem 0 2rem 0;
        padding: 1.2rem 1.1rem;
        font-size: 1rem;
        color: #222;
        box-shadow: 0 1px 4px rgba(30,41,59,0.06);
        border: 1px solid #e5e7eb;
    }
    .appointment-details-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .appointment-details-table th,
    .appointment-details-table td {
        text-align: left;
        padding: 0.4em 0.7em;
        font-size: 1rem;
        color: #222;
    }
    .appointment-details-table th {
        color: #0057b8;
        font-weight: 600;
        background: none;
        border: none;
        width: 38%;
    }
    .appointment-details-table td {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    @media only screen and (max-width: 600px) {
        .appointment-details {
            padding: 0.7rem 0.4rem;
        }
        .appointment-details-table th,
        .appointment-details-table td {
            font-size: 0.97rem;
            padding: 0.3em 0.4em;
        }
    }

    /* Status Badge */
    .vpa-status-badge {
        display: inline-block;
        min-width: 90px;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 18px;
        background: #f1f5f9;
        color: #64748b;
        border: none;
        line-height: 1.6;
    }
    .vpa-status-badge.vpa-status-pending { background: #fef9c3; color: #b45309; }
    .vpa-status-badge.vpa-status-confirmed { background: #dcfce7; color: #15803d; }
    .vpa-status-badge.vpa-status-completed { background: #dbeafe; color: #2563eb; }
    .vpa-status-badge.vpa-status-cancelled { background: #fee2e2; color: #b91c1c; }
    .vpa-status-badge.vpa-status-no_show { background: #f3f4f6; color: #6b7280; }

    /* Button */
    .email-btn,
    .email-button,
    .vpa-email-btn {
        display: inline-block;
        background: #0057b8;
        color: #fff !important;
        font-weight: 600;
        font-size: 1.08rem;
        border-radius: 8px;
        padding: 0.75em 2em;
        text-decoration: none;
        margin: 1.2em 0 0.7em 0;
        border: none;
        box-shadow: 0 2px 8px rgba(0,87,184,0.07);
        transition: background 0.18s, box-shadow 0.18s;
    }
    .email-btn:hover,
    .email-button:hover,
    .vpa-email-btn:hover {
        background: #003366;
        color: #fff !important;
        box-shadow: 0 4px 16px rgba(0,87,184,0.13);
    }

    /* Success/Error Messages */
    .vpa-message-success {
        background: #e6f9f0;
        color: #15803d;
        border-left: 5px solid #22c55e;
        border-radius: 10px;
        padding: 1.1rem 1.3rem;
        margin: 1.2rem 0;
        font-size: 1.08rem;
        font-weight: 500;
    }
    .vpa-message-error {
        background: #fef2f2;
        color: #b91c1c;
        border-left: 5px solid #ef4444;
        border-radius: 10px;
        padding: 1.1rem 1.3rem;
        margin: 1.2rem 0;
        font-size: 1.08rem;
        font-weight: 500;
    }

    /* Footer */
    .email-footer {
        text-align: center;
        color: #64748b;
        font-size: 0.97rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
        margin-top: 2rem;
    }
    .email-footer a {
        color: #0057b8;
        text-decoration: underline;
    }

    /* Responsive Table Fix */
    @media only screen and (max-width: 600px) {
        .appointment-details-table,
        .appointment-details-table tbody,
        .appointment-details-table tr,
        .appointment-details-table th,
        .appointment-details-table td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box;
        }
        .appointment-details-table th {
            padding-top: 0.7em;
        }
        .appointment-details-table td {
            margin-bottom: 0.7em;
        }
    }

    /* Hide preheader text */
    .preheader {
        display: none !important;
        visibility: hidden;
        opacity: 0;
        color: transparent;
        height: 0;
        width: 0;
        max-height: 0;
        max-width: 0;
        overflow: hidden;
        mso-hide: all;
    }
    </style>
";