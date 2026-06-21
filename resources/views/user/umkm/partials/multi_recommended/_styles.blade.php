{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 1rem;
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
            /* Primary color */
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

        .marker-number {
            position: absolute;
            width: 22px;
            height: 22px;
            left: 50%;
            top: 50%;
            margin: -11px 0 0 -11px;
            background: white;
            border-radius: 50%;
            text-align: center;
            color: #F97316;
            font-weight: bold;
            font-size: 12px;
            line-height: 22px;
            z-index: 1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
    </style>
