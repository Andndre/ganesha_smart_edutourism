{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <style>
        #map {
            height: 250px;
            width: 100%;
            border-radius: 0.75rem;
            z-index: 10;
        }

        .custom-div-icon {
            background: transparent;
            border: none;
        }

        .marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            background: #F97316;
            position: absolute;
            transform: rotate(-45deg);
            left: 50%;
            top: 50%;
            margin: -15px 0 0 -15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
        }

        .marker-pin::after {
            content: '';
            width: 14px;
            height: 14px;
            background: #fff;
            border-radius: 50%;
        }
    </style>
