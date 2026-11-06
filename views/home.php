<?php
ob_start();
?>

<!-- ================= CSS GIAO DIỆN CHUẨN ================= -->
<style>
    :root {
        --primary-blue: #0284c7;
        --primary-navy: #0f172a;
        --border-color: #e2e8f0;
        --bg-light: #f8fafc;
    }

    body {
        background-color: #f8fafc;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #1e293b;
        margin: 0;
    }

    .landing-wrapper {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px 80px;
    }

    /* ================= HERO BANNER FULL WIDTH ================= */
    .hero-banner {
        position: relative;
        isolation: isolate;
        width: 100vw;
        min-height: 390px;
        margin-left: calc(50% - 50vw);
        padding: 30px max(5%, calc((100vw - 1400px) / 2)) 75px;
        box-sizing: border-box;
        overflow: hidden;
        background: #0b3154;
    }

    .hero-banner::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(90deg, rgba(5, 31, 58, .94) 0%, rgba(5, 43, 77, .83) 55%, rgba(5, 53, 91, .72) 100%), var(--hero-bg);
        background-size: cover;
        background-position: center 45%;
        background-repeat: no-repeat;
    }

    .hero-grid {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) minmax(280px, .85fr);
        align-items: center;
        gap: 30px;
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .hero-left {
        min-width: 0;
        color: #fff;
    }

    .hero-tag {
        display: inline-block;
        margin-bottom: 12px;
        color: #93d5ff;
        font-size: 12px;
        font-weight: 700;
    }

    .hero-title {
        margin: 0 0 14px;
        color: #fff;
        font-size: clamp(28px, 3.2vw, 44px);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -1px;
    }

    .hero-title br+* {
        color: #70c5ff;
    }

    .hero-desc {
        max-width: 650px;
        margin: 0 0 18px;
        color: #d5e6f5;
        font-size: 14px;
        line-height: 1.8;
    }

    .hero-carousel-dots {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
        color: #c5d9e9;
        font-size: 11px;
    }

    .hero-carousel-dots .dot {
        width: 9px;
        height: 7px;
        border-radius: 10px;
        background: #7896aa;
    }

    .hero-carousel-dots .dot.active {
        width: 22px;
        background: #38bdf8;
    }

    .hero-cta-btns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        max-width: 680px;
    }

    .hero-cta-btns a {
        display: flex;
        min-height: 54px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 10px 16px;
        border-radius: 9px;
        text-align: center;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: .2s ease;
    }

    .btn-hero-primary {
        color: #fff;
        background: linear-gradient(110deg, #2154db, #078fca);
        border: 1px solid transparent;
    }

    .btn-hero-primary:hover {
        color: #fff;
        filter: brightness(1.1);
        transform: translateY(-2px);
    }

    .btn-hero-secondary {
        color: #17416b;
        background: #fff;
        border: 1px solid #dbeafe;
    }

    .btn-hero-secondary:hover {
        color: #075985;
        background: #eff8ff;
    }

    /* STATUS CARD */
    .hero-right {
        width: 100%;
    }

    .hero-status-card {
        padding: 20px;
        color: #fff;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 20px;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, .12);
    }

    .status-card-header h3 {
        margin: 0 0 5px;
        color: #fff;
        font-size: 17px;
        font-weight: 700;
    }

    .status-card-header p {
        margin: 0 0 15px;
        color: #c4def2;
        font-size: 11px;
    }

    .pill-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 16px;
    }

    .pill-tab {
        flex: 1;
        padding: 8px 5px;
        color: #e0efff;
        background: rgba(15, 45, 75, .4);
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 11px;
        cursor: pointer;
    }

    .pill-tab.active {
        color: #07539a;
        background: #fff;
        font-weight: 700;
    }

    .percent-number {
        margin: 5px 0;
        color: #fff;
        font-size: 34px;
        font-weight: 800;
    }

    .percent-sub {
        margin-bottom: 12px;
        color: #d4e7f8;
        font-size: 12px;
    }

    .custom-progress {
        height: 8px;
        overflow: hidden;
        background: rgba(255, 255, 255, .2);
        border-radius: 20px;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #38bdf8 0%, #38bdf8 75%, #fbbf24 75%);
        border-radius: 20px;
    }

    .status-alert-badge {
        margin: 14px 0;
        color: #6ee7b7;
        font-size: 12px;
        font-weight: 700;
    }

    .mini-info-box-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .mini-info-box-grid>div {
        padding: 10px;
        background: rgba(255, 255, 255, .1);
        border-radius: 8px;
    }

    .mini-box-label {
        margin-bottom: 5px;
        color: #b9d8ef;
        font-size: 10px;
    }

    .mini-box-val {
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    /* STATS NỔI TRÊN HERO */
    .stats-float-row {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        max-width: 1400px;
        margin: -40px auto 35px;
        padding: 0 5px;
    }

    .stat-box-card {
        min-width: 0;
        padding: 18px;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-top: 4px solid #2563eb;
        border-radius: 14px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .1);
    }

    .stat-box-card:nth-child(2) {
        border-top-color: #6366f1;
    }

    .stat-box-card:nth-child(3) {
        border-top-color: #f59e0b;
    }

    .stat-box-card:nth-child(4) {
        border-top-color: #10b981;
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .hero-grid {
            grid-template-columns: 1fr;
        }

        .hero-right {
            max-width: 500px;
        }

        .stats-float-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .hero-banner {
            padding: 30px 18px 65px;
        }

        .hero-title {
            font-size: 29px;
        }

        .hero-cta-btns {
            grid-template-columns: 1fr;
        }

        .stats-float-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: -30px;
        }

        .stat-box-card {
            padding: 13px;
        }

        .hero-status-card {
            padding: 16px;
        }
    }

    .status-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .status-card-header h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
    }

    .status-card-header p {
        margin: 3px 0 0;
        font-size: 11px;
        color: #94a3b8;
    }

    .pill-tabs {
        display: flex;
        background: rgba(0, 0, 0, 0.35);
        padding: 3px;
        border-radius: 8px;
        gap: 2px;
    }

    .pill-tab {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 12px;
        color: #94a3b8;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .pill-tab.active {
        background: #ffffff;
        color: #0f172a;
        font-weight: 600;
    }

    .percent-number {
        font-size: 2.6rem;
        font-weight: 800;
        margin: 12px 0 2px;
        color: #ffffff;
    }

    .percent-sub {
        font-size: 12px;
        color: #cbd5e1;
        margin-bottom: 14px;
    }

    .custom-progress {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 999px;
        height: 6px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .progress-bar-fill {
        background: #38bdf8;
        height: 100%;
        border-radius: 999px;
    }

    .status-alert-badge {
        font-size: 12px;
        color: #38bdf8;
        font-weight: 600;
        margin-bottom: 18px;
    }

    .mini-info-box-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 16px;
    }

    .mini-box-label {
        font-size: 10px;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 700;
    }

    .mini-box-val {
        font-size: 14px;
        font-weight: 700;
        margin-top: 2px;
        color: #f8fafc;
    }

    /* Floating Stats Row */
    .stats-float-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: -36px;
        position: relative;
        z-index: 10;
        padding: 0 16px;
    }

    .stat-box-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .stat-title {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-icon {
        color: #0284c7;
        font-size: 16px;
    }

    .stat-val {
        font-size: 2.1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .stat-val.val-green {
        color: #10b981;
    }

    .stat-note {
        font-size: 12px;
        color: #94a3b8;
    }


    /* ================= STATS FLOAT CARDS ================= */
    .stats-float-row {
        position: relative;
        z-index: 5;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        width: 100%;
        max-width: 1400px;
        margin: -40px auto 35px;
        padding: 0 5px;
        box-sizing: border-box;
    }

    /* STAT CARD */
    .stat-box-card {
        position: relative;
        min-width: 0;
        min-height: 120px;
        padding: 17px 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-top: 4px solid #2563eb;
        border-radius: 14px;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .10);
        box-sizing: border-box;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .stat-box-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 9px 24px rgba(15, 23, 42, .14);
    }

    /* MÀU VIỀN TỪNG THẺ */
    .stat-box-card.stat-blue {
        border-top-color: #2563eb;
    }

    .stat-box-card.stat-purple {
        border-top-color: #6366f1;
    }

    .stat-box-card.stat-orange {
        border-top-color: #f59e0b;
    }

    .stat-box-card.stat-green {
        border-top-color: #10b981;
    }

    /* HEADER */
    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 9px;
    }

    .stat-title {
        color: #8290a5;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .2px;
    }

    /* ICON */
    .stat-icon {
        flex-shrink: 0;
        color: #2563eb;
        font-size: 16px;
    }

    .stat-purple .stat-icon {
        color: #6366f1;
    }

    .stat-orange .stat-icon {
        color: #f59e0b;
    }

    .stat-green .stat-icon {
        color: #10b981;
    }

    /* GIÁ TRỊ */
    .stat-val {
        margin-bottom: 8px;
        color: #142e5d;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -.5px;
    }

    .stat-green .stat-val {
        color: #10b981;
    }

    /* MÔ TẢ */
    .stat-note {
        color: #8290a5;
        font-size: 11px;
        font-weight: 500;
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .stats-float-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
    }

    @media (max-width: 480px) {
        .stats-float-row {
            gap: 9px;
            padding: 0 10px;
        }

        .stat-box-card {
            min-height: 110px;
            padding: 13px;
        }

        .stat-title {
            font-size: 10px;
        }

        .stat-val {
            font-size: 25px;
        }

        .stat-note {
            font-size: 10px;
        }
    }



    /* ================= BANNER TÍNH NĂNG ================= */
    .highlight-banners-grid {
        display: grid;
        grid-template-columns: 1.35fr .9fr;
        grid-template-rows: repeat(2, minmax(135px, 1fr));
        gap: 16px;
        width: 100%;
        margin: 30px 0;
    }

    .banner-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        overflow: hidden;
        padding: 24px;
        border-radius: 16px;
        isolation: isolate;
        box-sizing: border-box;
        background: #0b4778;
    }

    /* BANNER LỚN */
    .banner-navy {
        grid-column: 1;
        grid-row: 1 / 3;
        min-height: 285px;
        color: #fff;
    }

    /* BANNER NHỎ */
    .banner-sky {
        grid-column: 2;
        grid-row: 1;
        min-height: 135px;
        color: #fff;
    }

    .banner-blue {
        grid-column: 2;
        grid-row: 2;
        min-height: 135px;
        color: #fff;
    }

    /* ẢNH NỀN */
    .banner-bg-image {
        position: absolute;
        inset: 0;
        z-index: -2;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform .4s ease;
    }

    .banner-card:hover .banner-bg-image {
        transform: scale(1.04);
    }

    /* LỚP PHỦ ẢNH */
    .banner-overlay {
        position: absolute;
        inset: 0;
        z-index: -1;
        background: linear-gradient(100deg, rgba(5, 35, 65, .92), rgba(5, 65, 110, .72));
    }

    .banner-sky .banner-overlay {
        background: linear-gradient(100deg, rgba(5, 47, 82, .86), rgba(9, 75, 125, .68));
    }

    .banner-blue .banner-overlay {
        background: linear-gradient(100deg, rgba(4, 43, 83, .92), rgba(5, 74, 130, .76));
    }

    /* NỘI DUNG */
    .banner-content {
        position: relative;
        z-index: 1;
    }

    .banner-badge {
        display: inline-block;
        margin-bottom: 12px;
        color: #93d5ff;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .banner-title {
        margin: 0 0 10px;
        color: #fff;
        font-size: 23px;
        font-weight: 800;
        line-height: 1.35;
    }

    .banner-desc {
        margin: 0;
        color: #e0edf8;
        font-size: 12px;
        line-height: 1.7;
    }

    .banner-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .banner-actions .btn-hero-primary,
    .banner-actions .btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .banner-actions .btn-hero-primary {
        color: #fff;
        background: #078bd5;
    }

    .banner-actions .btn-hero-secondary {
        color: #07518b;
        background: #fff;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .highlight-banners-grid {
            grid-template-columns: 1fr;
            grid-template-rows: auto;
        }

        .banner-navy,
        .banner-sky,
        .banner-blue {
            grid-column: 1;
            grid-row: auto;
            min-height: 180px;
        }

        .banner-navy {
            min-height: 260px;
        }

        .banner-title {
            font-size: 19px;
        }
    }


    /* 8 Core Procedures Section */
    .section-title-wrap {
        margin-top: 64px;
        margin-bottom: 24px;
    }

    .section-super-title {
        color: #0284c7;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .section-main-title {
        font-size: 1.85rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 8px;
    }

    .section-subtitle {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    .filter-tabs-row {
        display: flex;
        gap: 8px;
        margin-bottom: 28px;
        overflow-x: auto;
        padding-bottom: 6px;
    }

    .filter-tab-btn {
        background: #ffffff;
        border: 1px solid var(--border-color);
        padding: 7px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        white-space: nowrap;
    }

    .filter-tab-btn.active {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    .features-card-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .feature-clean-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .feature-clean-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04);
    }

    .feature-top-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }

    .feature-icon-badge {
        width: 32px;
        height: 32px;
        background: #eff6ff;
        color: #0284c7;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .feature-id {
        font-size: 11px;
        font-weight: 800;
        color: #0284c7;
        letter-spacing: 0.5px;
    }

    .card-item-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 10px;
        line-height: 1.4;
    }

    .card-item-desc {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.55;
        margin: 0 0 18px;
    }

    .card-tag-pill {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 6px;
        margin-bottom: 14px;
        font-weight: 500;
    }

    .card-link-detail {
        color: #0284c7;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Self-Service Section */

    /* ================= CORE FEATURES SECTION ================= */
    .features-section {
        width: 100%;
        padding: 35px 0 30px;
        background: #f6f8fb;
        box-sizing: border-box;
    }

    /* TIÊU ĐỀ SECTION */
    .features-section .section-title-wrap {
        max-width: 1200px;
        margin: 0 auto 18px;
    }

    .features-section .section-super-title {
        margin-bottom: 12px;
        color: #2563eb;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .features-section .section-main-title {
        margin: 0 0 12px;
        color: #142e5d;
        font-size: 25px;
        font-weight: 800;
        line-height: 1.3;
    }

    .features-section .section-subtitle {
        margin: 0;
        color: #8290a5;
        font-size: 13px;
        line-height: 1.6;
    }

    /* FILTER TABS */
    .features-section .filter-tabs-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        max-width: 1200px;
        margin: 0 auto 16px;
    }

    .features-section .filter-tab-btn {
        padding: 8px 13px;
        color: #64748b;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
    }

    .features-section .filter-tab-btn:hover {
        color: #2563eb;
        border-color: #93c5fd;
        background: #eff6ff;
    }

    .features-section .filter-tab-btn.active {
        color: #fff;
        background: #142e5d;
        border-color: #142e5d;
        box-shadow: 0 2px 5px rgba(20, 46, 93, .15);
    }

    /* GRID 8 QUY TRÌNH */
    .features-section .features-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* FEATURE CARD */
    .features-section .feature-clean-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 0;
        min-height: 255px;
        padding: 15px;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 13px;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .025);
        box-sizing: border-box;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .features-section .feature-clean-card:hover {
        transform: translateY(-3px);
        border-color: #bfdbfe;
        box-shadow: 0 7px 18px rgba(15, 23, 42, .07);
    }

    /* ICON + MÃ QUY TRÌNH */
    .features-section .feature-top-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .features-section .feature-icon-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        color: #2563eb;
        background: #eff6ff;
        border-radius: 8px;
        font-size: 17px;
    }

    .features-section .feature-id {
        color: #2563eb;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .2px;
    }

    /* TIÊU ĐỀ CARD */
    .features-section .card-item-title {
        margin: 0 0 10px;
        color: #142e5d;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.55;
    }

    /* MÔ TẢ CARD */
    .features-section .card-item-desc {
        margin: 0 0 12px;
        color: #718096;
        font-size: 11px;
        font-weight: 400;
        line-height: 1.65;
    }

    /* TAG */
    .features-section .card-tag-pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        margin-bottom: 11px;
        color: #718096;
        background: #f1f5f9;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }

    /* NÚT CHI TIẾT */
    .features-section .card-link-detail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        padding: 9px 12px;
        color: #1681d5;
        background: #eff6ff;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .features-section .card-link-detail:hover {
        color: #fff;
        background: #2563eb;
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
        .features-section .features-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .features-section .section-title-wrap,
        .features-section .filter-tabs-row,
        .features-section .features-card-grid {
            width: calc(100% - 40px);
        }
    }

    @media (max-width: 768px) {
        .features-section .features-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .features-section .feature-clean-card {
            min-height: 250px;
            padding: 13px;
        }

        .features-section .section-main-title {
            font-size: 22px;
        }
    }

    @media (max-width: 480px) {
        .features-section {
            padding: 25px 0;
        }

        .features-section .features-card-grid {
            grid-template-columns: 1fr;
        }

        .features-section .filter-tabs-row {
            gap: 6px;
        }

        .features-section .filter-tab-btn {
            padding: 7px 9px;
            font-size: 10px;
        }

        .features-section .feature-clean-card {
            min-height: 0;
            gap: 15px;
        }
    }


    .filter-sidebar {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }

    .sidebar-title {
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 16px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .form-control-custom {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #ffffff;
        font-size: 13px;
        color: #1e293b;
        box-sizing: border-box;
    }

    .filter-tip-box {
        background: #e0f2fe;
        border-left: 3px solid #0284c7;
        padding: 10px 12px;
        border-radius: 4px;
        font-size: 12px;
        color: #0369a1;
        margin-top: 18px;
        line-height: 1.45;
    }

    .room-results-area {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .room-card-item {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        transition: border-color 0.2s;
    }

    .room-card-item:hover {
        border-color: #0284c7;
    }

    .room-main-info {
        display: flex;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 4px;
    }

    .room-code {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
    }

    .room-sub-badge {
        font-size: 11px;
        background: #f1f5f9;
        color: #0284c7;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 600;
    }

    .room-meta-desc {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 6px;
    }

    .room-price-row {
        font-size: 13px;
        color: #0f172a;
    }

    .price-bold {
        font-weight: 700;
    }

    .beds-available-tag {
        color: #10b981;
        font-weight: 600;
        margin-left: 8px;
    }

    .btn-book-room {
        background: #0284c7;
        color: #ffffff;
        padding: 9px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }

    @media (max-width: 992px) {

        .hero-grid,
        .highlight-banners-grid,
        .service-layout-grid {
            grid-template-columns: 1fr;
        }

        .stats-float-row,
        .features-card-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .stats-float-row {
            margin-top: 16px;
            padding: 0;
        }
    }

    @media (max-width: 600px) {

        .stats-float-row,
        .features-card-grid {
            grid-template-columns: 1fr;
        }

        .top-navbar-container {
            flex-direction: column;
            gap: 12px;
        }
    }


    /* ================= SELF SERVICE SECTION ================= */
    .self-service-box {
        width: 100%;
        max-width: 910px;
        margin: 35px auto;
        padding: 22px;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 19px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .055);
        box-sizing: border-box;
    }

    /* HEADER */
    .self-service-box .service-header {
        margin-bottom: 18px;
    }

    .self-service-box .section-super-title {
        margin-bottom: 8px;
        color: #1681d5;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .4px;
    }

    .self-service-box .section-main-title {
        margin: 0 0 7px;
        color: #142e4f;
        font-size: 21px;
        font-weight: 800;
        line-height: 1.35;
    }

    .self-service-box .section-subtitle {
        margin: 0;
        color: #8290a5;
        font-size: 11px;
        line-height: 1.6;
    }

    /* SERVICE TABS */
    .self-service-box .service-nav-tabs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .self-service-box .service-tab-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 7px 12px;
        color: #53657b;
        background: #f1f5f9;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
    }

    .self-service-box .service-tab-btn:hover {
        color: #0875c5;
        background: #e8f3ff;
    }

    .self-service-box .service-tab-btn.active {
        color: #fff;
        background: #0878c9;
        border-color: #0878c9;
        box-shadow: 0 2px 5px rgba(8, 120, 201, .15);
    }

    /* MAIN TWO COLUMNS */
    .self-service-box .service-layout-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: start;
        gap: 17px;
        padding-top: 19px;
    }

    /* FILTER SIDEBAR */
    .self-service-box .filter-sidebar {
        min-width: 0;
        padding: 16px;
        background: #f5f8fb;
        border: 1px solid #e5edf5;
        border-radius: 13px;
        box-sizing: border-box;
    }

    .self-service-box .sidebar-title {
        margin: 0 0 15px;
        color: #172b49;
        font-size: 14px;
        font-weight: 800;
    }

    /* FORM GROUP */
    .self-service-box .form-group {
        margin-bottom: 13px;
    }

    .self-service-box .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 10px;
        font-weight: 700;
    }

    .self-service-box .form-control-custom {
        display: block;
        width: 100%;
        height: 34px;
        padding: 0 10px;
        color: #26374d;
        background: #fff;
        border: 1px solid #d8e1eb;
        border-radius: 7px;
        outline: none;
        font-family: inherit;
        font-size: 11px;
        transition: border-color .2s ease, box-shadow .2s ease;
        box-sizing: border-box;
    }

    .self-service-box .form-control-custom:focus {
        border-color: #3996dc;
        box-shadow: 0 0 0 3px rgba(57, 150, 220, .1);
    }

    /* FILTER DESCRIPTION */
    .self-service-box .filter-description {
        margin: 12px 0;
        color: #8290a5;
        font-size: 10px;
        line-height: 1.6;
    }

    /* FILTER TIP */
    .self-service-box .filter-tip-box {
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding: 11px;
        color: #2676b6;
        background: #dfeeff;
        border-radius: 9px;
        font-size: 10px;
        line-height: 1.55;
    }

    .self-service-box .filter-tip-box strong {
        font-weight: 700;
    }

    .self-service-box .filter-tip-box span {
        color: #3981b8;
    }

    /* ROOM RESULTS */
    .self-service-box .room-results-area {
        min-width: 0;
    }

    .self-service-box .room-results-heading {
        margin-bottom: 7px;
        color: #8290a5;
        font-size: 10px;
    }

    /* ROOM LIST */
    .self-service-box .room-list {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    /* ROOM CARD */
    .self-service-box .room-card-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-width: 0;
        min-height: 75px;
        padding: 11px 12px;
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 13px;
        box-sizing: border-box;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .self-service-box .room-card-item:hover {
        border-color: #9bcdf1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .045);
    }

    .self-service-box .room-information {
        flex: 1;
        min-width: 0;
    }

    /* ROOM CODE */
    .self-service-box .room-main-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 5px;
    }

    .self-service-box .room-code {
        color: #172b49;
        font-size: 13px;
        font-weight: 800;
    }

    .self-service-box .room-sub-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 6px;
        color: #3286c5;
        background: #e5f2ff;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* ROOM DESCRIPTION */
    .self-service-box .room-meta-desc {
        margin-bottom: 4px;
        color: #8290a5;
        font-size: 9px;
        line-height: 1.5;
    }

    /* ROOM PRICE */
    .self-service-box .room-price-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        font-size: 10px;
    }

    .self-service-box .price-bold {
        color: #172b49;
        font-weight: 800;
    }

    .self-service-box .beds-available-tag {
        color: #10a779;
        font-weight: 600;
    }

    /* BOOK BUTTON */
    .self-service-box .btn-book-room {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        min-width: 79px;
        min-height: 31px;
        padding: 7px 10px;
        color: #fff;
        background: #0878c9;
        border: 1px solid #0878c9;
        border-radius: 7px;
        font-family: inherit;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
        cursor: pointer;
        transition: all .2s ease;
    }

    .self-service-box .btn-book-room:hover {
        background: #0565aa;
        border-color: #0565aa;
        box-shadow: 0 3px 8px rgba(8, 120, 201, .2);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .self-service-box {
            width: calc(100% - 24px);
            margin: 25px auto;
            padding: 16px;
            border-radius: 14px;
        }

        .self-service-box .service-layout-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .self-service-box .section-main-title {
            font-size: 19px;
        }
    }

    @media (max-width: 480px) {
        .self-service-box {
            padding: 13px;
        }

        .self-service-box .service-nav-tabs {
            gap: 5px;
        }

        .self-service-box .service-tab-btn {
            padding: 7px 9px;
            font-size: 9px;
        }

        .self-service-box .filter-sidebar {
            padding: 13px;
        }

        .self-service-box .room-card-item {
            padding: 10px;
            gap: 6px;
        }

        .self-service-box .room-code {
            font-size: 12px;
        }

        .self-service-box .btn-book-room {
            min-width: 70px;
            padding: 7px;
            font-size: 9px;
        }
    }
</style>

<!-- ================= PHẦN THÂN NỘI DUNG ================= -->
<div class="landing-wrapper">
    <div class="hero-stats-section" style="--hero-bg: url('<?= url('assets/images/img1.jpg') ?>');">
        <!-- Hero Section -->
        <div class="hero-banner">
            <div class="hero-grid">
                <div class="hero-left">
                    <span class="hero-tag">Đợt đăng ký lưu trú mới &bull; Row-Level Locking</span>
                    <h1 class="hero-title">Không gian phòng ở chuẩn mực<br>Xét duyệt & giữ chỗ 72 giờ</h1>
                    <p class="hero-desc">
                        Lọc điều kiện lưu trú, xét duyệt hồ sơ và kiểm soát thời hạn thanh toán. Minh bạch từng bước, an tâm mỗi lần giữ chỗ.
                    </p>

                    <div class="hero-carousel-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span>Tạm dừng</span>
                        <span style="margin-left: 10px;">Banner 02/03 - Phòng ở & giữ chỗ 72h</span>
                    </div>

                    <div class="hero-cta-btns">
                        <?php if (class_exists('\App\Core\Auth') && \App\Core\Auth::check()): ?>
                            <?php if (\App\Core\Auth::isSinhVien()): ?>
                                <a href="<?= url('student/dashboard') ?>" class="btn-hero-primary">Vào Cổng sinh viên &rarr;</a>
                            <?php elseif (\App\Core\Auth::isNhanVien()): ?>
                                <a href="<?= url('staff/dashboard') ?>" class="btn-hero-primary">Vào BQL cụm nhà &rarr;</a>
                            <?php elseif (\App\Core\Auth::isQuanTriVien()): ?>
                                <a href="<?= url('admin/dashboard') ?>" class="btn-hero-primary">Vào Quản trị hệ thống &rarr;</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= function_exists('url') ? url('register') : '#register' ?>" class="btn-hero-primary">
                                Dành cho sinh viên chưa ở KTX<br><small style="font-weight: 400; font-size: 11px;">Đăng ký lưu trú ngay</small>
                            </a>
                            <a href="<?= function_exists('url') ? url('login') : '#login' ?>" class="btn-hero-secondary">
                                Dành cho sinh viên đang ở KTX<br><small style="font-weight: 400; font-size: 11px; color: #64748b;">Đăng nhập cổng nội trú</small>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hero Right: Realtime status box -->
                <div class="hero-right">
                    <div class="hero-status-card">
                        <div class="status-card-header">
                            <div>
                                <h3>Trạng thái Giường Nội trú</h3>
                                <p>Dữ liệu mô phỏng &bull; Không phải dữ liệu trực tuyến</p>
                            </div>
                            <div class="pill-tabs">
                                <button class="pill-tab active">Toàn KTX</button>
                                <button class="pill-tab">Khu A</button>
                                <button class="pill-tab">Khu B</button>
                            </div>
                        </div>

                        <div class="percent-number">36,8%</div>
                        <div class="percent-sub">28 / 76 giường đang ở</div>

                        <div class="custom-progress">
                            <div class="progress-bar-fill" style="width: 36.8%;"></div>
                        </div>

                        <div class="status-alert-badge">&bull; 48 giường trống &bull; Sẵn sàng tiếp nhận</div>

                        <div class="mini-info-box-grid">
                            <div>
                                <div class="mini-box-label">Đang khóa giữ chỗ 72h</div>
                                <div class="mini-box-val">08 hồ sơ</div>
                            </div>
                            <div>
                                <div class="mini-box-label">Cơ chế chống trùng</div>
                                <div class="mini-box-val">Row-Level Lock</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- STATS SECTION -->
        <?php
        $statsDefaults = [
            'total_students' => 25,
            'total_rooms' => 15,
            'total_beds' => 76,
            'available_beds' => 48,
        ];
        $stats = isset($stats) && is_array($stats) ? $stats : [];
        $homeStats = array_merge($statsDefaults, $stats);
        ?>

        <div id="stats" class="stats-float-row">
            <div class="stat-box-card stat-blue">
                <div class="stat-header">
                    <span class="stat-title">SINH VIÊN ĐĂNG KÝ</span>
                    <span class="stat-icon">&#128101;</span>
                </div>
                <div class="stat-val"><?= number_format($homeStats['total_students']) ?></div>
                <div class="stat-note">Hồ sơ trong hệ thống</div>
            </div>

            <div class="stat-box-card stat-purple">
                <div class="stat-header">
                    <span class="stat-title">TỔNG SỐ PHÒNG Ở</span>
                    <span class="stat-icon">&#127970;</span>
                </div>
                <div class="stat-val"><?= number_format($homeStats['total_rooms']) ?></div>
                <div class="stat-note">Khu A và Khu B</div>
            </div>

            <div class="stat-box-card stat-orange">
                <div class="stat-header">
                    <span class="stat-title">TỔNG SỐ GIƯỜNG</span>
                    <span class="stat-icon">&#128716;</span>
                </div>
                <div class="stat-val"><?= number_format($homeStats['total_beds']) ?></div>
                <div class="stat-note">Công suất thiết kế</div>
            </div>

            <div class="stat-box-card stat-green">
                <div class="stat-header">
                    <span class="stat-title">GIƯỜNG CÒN TRỐNG</span>
                    <span class="stat-icon">&#10003;</span>
                </div>
                <div class="stat-val"><?= number_format($homeStats['available_beds']) ?></div>
                <div class="stat-note">Sẵn sàng tiếp nhận</div>
            </div>
        </div>

    </div>


    <!-- 3 BANNERS TÍNH NĂNG NỔI BẬT -->
    <div class="highlight-banners-grid">
        <!-- BANNER LỚN -->
        <div class="banner-card banner-navy">
            <img src="<?= url('assets/images/img2.jpg') ?>" alt="Ký túc xá sinh viên" class="banner-bg-image">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <span class="banner-badge">ĐỢT ĐĂNG KÝ NỘI TRÚ &bull; NĂM HỌC 2026 &ndash; 2027</span>
                <h2 class="banner-title">Không gian phòng ở hiện đại &amp; Xét duyệt tự động trong 72 giờ</h2>
                <p class="banner-desc">Lọc tòa nhà theo giới tính, kiểm soát điều kiện lưu trú và khóa dòng giữ chỗ. Bắt đầu hành trình nội trú ngay hôm nay.</p>
                <div class="banner-actions">
                    <a href="#services" class="btn-hero-primary">Đăng ký chọn phòng ngay</a>
                    <a href="#services" class="btn-hero-secondary">Xem danh sách phòng trống</a>
                </div>
            </div>
        </div>

        <!-- BANNER AN NINH -->
        <div class="banner-card banner-sky">
            <img src="<?= url('assets/images/img4.jpg') ?>" alt="Hệ thống an ninh ký túc xá" class="banner-bg-image">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <span class="banner-badge">AN NINH NỘI TRÚ THÔNG MINH</span>
                <h3 class="banner-title">Kiểm soát cổng thẻ từ &amp; Kết xuất điểm rèn luyện</h3>
                <p class="banner-desc">Đối soát đơn tạm vắng và cảnh báo vắng mặt quá 30 ngày không lý do.</p>
            </div>
        </div>

        <!-- BANNER TIỆN ÍCH -->
        <div class="banner-card banner-blue">
            <img src="<?= url('assets/images/img3.jpg') ?>" alt="Tiện ích nội trú sinh viên" class="banner-bg-image">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <span class="banner-badge">TIỆN ÍCH SỐ HÓA SINH VIÊN</span>
                <h3 class="banner-title">Thanh toán điện nước lũy tiến &amp; Báo hỏng trực tuyến</h3>
                <p class="banner-desc">Cảnh báo chỉ số bất thường &gt; 3 lần trung bình 3 tháng và chống trùng phiếu bảo trì.</p>
            </div>
        </div>
    </div>


    <!-- 8 Business Features Showcase -->
    <div id="core-features" class="section-title-wrap">
        <div class="section-super-title">TIÊU CHUẨN VẬN HÀNH</div>
        <h2 class="section-main-title">8 quy trình nghiệp vụ cốt lõi</h2>
        <p class="section-subtitle">Một hệ thống thống nhất. Mỗi bước lưu trú đều được kiểm soát rõ ràng.</p>
    </div>

    <div class="filter-tabs-row">
        <button class="filter-tab-btn active">Tất cả (8)</button>
        <button class="filter-tab-btn">Xếp chỗ &amp; Phòng ở (4)</button>
        <button class="filter-tab-btn">Tài chính &amp; Điện nước (2)</button>
        <button class="filter-tab-btn">Bảo trì &amp; Nội vụ SV (2)</button>
    </div>

    <div class="features-card-grid">
        <!-- QT 01 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#128274;</div>
                    <span class="feature-id">QUY TRÌNH 01</span>
                </div>
                <h4 class="card-item-title">Khóa giường &amp; Kiểm soát điều kiện lưu trú</h4>
                <p class="card-item-desc">Tự động lọc giới tính tòa nhà, kiểm tra thời gian đào tạo chuẩn theo trường ĐH. Giao dịch CSDL và Row-Level Locking ngăn xung đột giữ chỗ cùng lúc.</p>
            </div>
            <div>
                <span class="card-tag-pill">Lưu trú</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 02 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#128196;</div>
                    <span class="feature-id">QUY TRÌNH 02</span>
                </div>
                <h4 class="card-item-title">Duyệt hồ sơ &amp; Tự động thu hồi chỗ sau 72 giờ</h4>
                <p class="card-item-desc">Thiết lập hạn thanh toán 72 giờ sau khi duyệt. Dịch vụ chạy ngầm định kỳ quét hồ sơ quá hạn, thu hồi giường về trạng thái trống.</p>
            </div>
            <div>
                <span class="card-tag-pill">72 giờ</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 03 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#127970;</div>
                    <span class="feature-id">QUY TRÌNH 03</span>
                </div>
                <h4 class="card-item-title">Dồn phòng &amp; Tối ưu công suất lưu trú</h4>
                <p class="card-item-desc">Thống kê phòng có tỷ lệ lấp đầy &le; 50% vào giữa năm học. Đề xuất chuyển sinh viên sang phòng cùng loại trong cụm, giải phóng phòng bảo trì.</p>
            </div>
            <div>
                <span class="card-tag-pill">Công suất</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 04 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#8646;</div>
                    <span class="feature-id">QUY TRÌNH 04</span>
                </div>
                <h4 class="card-item-title">Chuyển phòng &amp; Bù trừ chênh lệch chi phí</h4>
                <p class="card-item-desc">Xác định số tháng hợp đồng còn lại, đối chiếu đơn giá phòng mới và cũ để phát hành hóa đơn nộp bổ sung hoặc phiếu hoàn trả.</p>
            </div>
            <div>
                <span class="card-tag-pill">Tài chính</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 05 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#128181;</div>
                    <span class="feature-id">QUY TRÌNH 05</span>
                </div>
                <h4 class="card-item-title">Xét miễn giảm theo trần &amp; Hoàn phí trước hạn</h4>
                <p class="card-item-desc">Áp trần theo đơn giá phòng tiêu chuẩn cho diện chính sách. Xác thực tốt nghiệp, bảo lưu, du học trong 07 ngày để hoàn phí các tháng chưa ở.</p>
            </div>
            <div>
                <span class="card-tag-pill">Chính sách</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 06 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#9889;</div>
                    <span class="feature-id">QUY TRÌNH 06</span>
                </div>
                <h4 class="card-item-title">Tính tiền điện nước bậc thang &amp; Kiểm tra bất thường</h4>
                <p class="card-item-desc">Cảnh báo chỉ số mới &lt; chỉ số cũ hoặc mức tiêu thụ &gt; 3 lần trung bình 3 tháng. Tự động áp biểu giá bậc thang lũy tiến.</p>
            </div>
            <div>
                <span class="card-tag-pill">Điện nước</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 07 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#128295;</div>
                    <span class="feature-id">QUY TRÌNH 07</span>
                </div>
                <h4 class="card-item-title">Điều phối bảo trì &amp; Chống trùng lặp phiếu</h4>
                <p class="card-item-desc">Kiểm tra phiếu báo hỏng đang xử lý trong cùng phòng. Nếu bạn cùng phòng đã gửi, điều hướng theo dõi tiến độ chung để tránh trùng phiếu.</p>
            </div>
            <div>
                <span class="card-tag-pill">Bảo trì</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>

        <!-- QT 08 -->
        <div class="feature-clean-card">
            <div>
                <div class="feature-top-meta">
                    <div class="feature-icon-badge">&#128737;</div>
                    <span class="feature-id">QUY TRÌNH 08</span>
                </div>
                <h4 class="card-item-title">Giám sát vắng mặt 30 ngày &amp; Báo cáo điểm rèn luyện</h4>
                <p class="card-item-desc">Đối soát quét thẻ với đơn tạm vắng đã duyệt. Cảnh báo vắng liên tục &gt; 30 ngày không lý do, kết xuất điểm rèn luyện gửi về trường thành viên.</p>
            </div>
            <div>
                <span class="card-tag-pill">An ninh</span><br>
                <a href="javascript:void(0)" class="card-link-detail">Chi tiết quy trình &rarr;</a>
            </div>
        </div>
    </div>


    <!-- CỔNG TIỆN ÍCH NỘI TRÚ TỰ PHỤC VỤ -->
    <div id="services" class="self-service-box">

        <!-- HEADER -->
        <div class="service-header">
            <div class="section-super-title">CỔNG TIỆN ÍCH NỘI TRÚ</div>
            <h2 class="section-main-title">Tra cứu &amp; tự phục vụ sinh viên</h2>
            <p class="section-subtitle">Trải nghiệm mô phỏng &bull; Không gửi hồ sơ, thu tiền hay giữ giường thật. Dữ liệu đặt lại khi tải lại trang.</p>
        </div>

        <!-- SERVICE TABS -->
        <div class="service-nav-tabs">
            <button type="button" class="service-tab-btn active">Tra cứu phòng &amp; Khóa giường 72h</button>
            <button type="button" class="service-tab-btn">Tính điện bậc thang &amp; Cảnh báo</button>
            <button type="button" class="service-tab-btn">Báo hỏng chống trùng phiếu</button>
        </div>

        <!-- MAIN CONTENT -->
        <div class="service-layout-grid">

            <!-- FILTER SIDEBAR -->
            <div class="filter-sidebar">
                <h4 class="sidebar-title">Tìm phòng phù hợp</h4>

                <div class="form-group">
                    <label for="serviceUniversity">Trường thành viên</label>
                    <select id="serviceUniversity" class="form-control-custom">
                        <option value="UIT">UIT</option>
                        <option value="BK">Bách Khoa</option>
                        <option value="KHTN">Khoa học Tự nhiên</option>
                        <option value="KHXHNV">KHXH&amp;NV</option>
                        <option value="KTPL">Kinh tế - Luật</option>
                        <option value="QT">Quốc Tế</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="serviceGender">Giới tính tòa nhà</label>
                    <select id="serviceGender" class="form-control-custom">
                        <option value="">Tất cả</option>
                        <option value="Nam">Nam</option>
                        <option value="Nu">Nữ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="serviceZone">Khu ký túc xá</label>
                    <select id="serviceZone" class="form-control-custom">
                        <option value="">Tất cả</option>
                        <option value="A">Khu A</option>
                        <option value="B">Khu B</option>
                    </select>
                </div>

                <p class="filter-description">Trường UIT: áp dụng bộ phòng mẫu chung. Chưa có dữ liệu điều kiện đào tạo theo trường.</p>

                <div class="filter-tip-box">
                    <strong>Quy trình 01 &amp; 02</strong>
                    <span>Mỗi thao tác giữ chỗ chỉ mô phỏng khóa dòng và thời hạn 72 giờ.</span>
                </div>
            </div>

            <!-- ROOM RESULTS -->
            <div class="room-results-area">
                <div class="room-results-heading">6 phòng phù hợp &bull; Dữ liệu mẫu</div>

                <div class="room-list">

                    <!-- ROOM 1 -->
                    <div class="room-card-item">
                        <div class="room-information">
                            <div class="room-main-info">
                                <span class="room-code">BA4-201</span>
                                <span class="room-sub-badge">Khu B &bull; Nam</span>
                            </div>
                            <div class="room-meta-desc">Cụm BA &bull; Phòng 4 SV &bull; Có máy lạnh</div>
                            <div class="room-price-row">
                                <span class="price-bold">650.000 đ/tháng</span>
                                <span class="beds-available-tag">Trống 2/4</span>
                            </div>
                        </div>
                        <button type="button" class="btn-book-room">Giữ chỗ 72h</button>
                    </div>

                    <!-- ROOM 2 -->
                    <div class="room-card-item">
                        <div class="room-information">
                            <div class="room-main-info">
                                <span class="room-code">BA2-405</span>
                                <span class="room-sub-badge">Khu B &bull; Nữ</span>
                            </div>
                            <div class="room-meta-desc">Cụm BA &bull; Phòng 6 SV &bull; Tiêu chuẩn</div>
                            <div class="room-price-row">
                                <span class="price-bold">260.000 đ/tháng</span>
                                <span class="beds-available-tag">Trống 3/6</span>
                            </div>
                        </div>
                        <button type="button" class="btn-book-room">Giữ chỗ 72h</button>
                    </div>

                    <!-- ROOM 3 -->
                    <div class="room-card-item">
                        <div class="room-information">
                            <div class="room-main-info">
                                <span class="room-code">AG1-108</span>
                                <span class="room-sub-badge">Khu A &bull; Nam</span>
                            </div>
                            <div class="room-meta-desc">Cụm AG &bull; Phòng 8 SV &bull; Tiêu chuẩn trần miễn giảm</div>
                            <div class="room-price-row">
                                <span class="price-bold">180.000 đ/tháng</span>
                                <span class="beds-available-tag">Trống 4/8</span>
                            </div>
                        </div>
                        <button type="button" class="btn-book-room">Giữ chỗ 72h</button>
                    </div>

                    <!-- ROOM 4 -->
                    <div class="room-card-item">
                        <div class="room-information">
                            <div class="room-main-info">
                                <span class="room-code">AH3-310</span>
                                <span class="room-sub-badge">Khu A &bull; Nữ</span>
                            </div>
                            <div class="room-meta-desc">Cụm AH &bull; Phòng 4 SV &bull; Dịch vụ</div>
                            <div class="room-price-row">
                                <span class="price-bold">650.000 đ/tháng</span>
                                <span class="beds-available-tag">Trống 1/4</span>
                            </div>
                        </div>
                        <button type="button" class="btn-book-room">Giữ chỗ 72h</button>
                    </div>

                    <!-- ROOM 5 -->
                    <div class="room-card-item">
                        <div class="room-information">
                            <div class="room-main-info">
                                <span class="room-code">BC1-502</span>
                                <span class="room-sub-badge">Khu B &bull; Nam</span>
                            </div>
                            <div class="room-meta-desc">Cụm BC &bull; Phòng 6 SV &bull; Tiêu chuẩn</div>
                            <div class="room-price-row">
                                <span class="price-bold">260.000 đ/tháng</span>
                                <span class="beds-available-tag">Trống 2/6</span>
                            </div>
                        </div>
                        <button type="button" class="btn-book-room">Giữ chỗ 72h</button>
                    </div>

                    <!-- ROOM 6 -->
                    <div class="room-card-item">
                        <div class="room-information">
                            <div class="room-main-info">
                                <span class="room-code">BD2-209</span>
                                <span class="room-sub-badge">Khu B &bull; Nữ</span>
                            </div>
                            <div class="room-meta-desc">Cụm BD &bull; Phòng 8 SV &bull; Tiêu chuẩn</div>
                            <div class="room-price-row">
                                <span class="price-bold">180.000 đ/tháng</span>
                                <span class="beds-available-tag">Trống 5/8</span>
                            </div>
                        </div>
                        <button type="button" class="btn-book-room">Giữ chỗ 72h</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
$title = "Hệ thống Quản lý Ký túc xá ĐHQG-HCM";
require __DIR__ . '/layouts/main.php';
?>