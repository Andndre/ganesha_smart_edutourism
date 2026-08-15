import { BalineseDate, BalineseDateUtil, SasihDayInfo, Rahinan } from 'balinese-date-js-lib';

const BIG_RAHINAN = [Rahinan.NYEPI, Rahinan.GALUNGAN, Rahinan.KUNINGAN];

// Hanya hari libur nasional bertanggal tetap. Yang tanggalnya berubah tiap tahun
// (Idul Fitri, Waisak, Imlek, dll) butuh sumber data eksternal, belum di-cover di sini.
const FIXED_NATIONAL_HOLIDAYS = {
    '01-01': 'Tahun Baru Masehi',
    '08-17': 'Hari Kemerdekaan RI',
    '12-25': 'Hari Natal',
};

function fixedHolidayName(date) {
    const key = String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    return FIXED_NATIONAL_HOLIDAYS[key] || null;
}

export function getBalineseInfo(date) {
    const bd = new BalineseDate(date);
    const allRahinan = BalineseDateUtil.getRahinan(bd);
    const isBigRahinan = allRahinan.some((r) => BIG_RAHINAN.includes(r));
    const nationalHoliday = fixedHolidayName(date);

    return {
        saka: bd.saka,
        sasih: bd.sasih.name,
        wuku: bd.wuku.name,
        pancaWara: bd.pancaWara.name,
        saptaWara: bd.saptaWara.name,
        isPurnama: bd.sasihDayInfo.id === SasihDayInfo.PURNAMA.id,
        isTilem: bd.sasihDayInfo.id === SasihDayInfo.TILEM.id,
        // Rahinan minus the moon-phase entries, those already get their own dot marker
        rahinan: allRahinan.map((r) => r.name).filter((name) => name !== 'Purnama' && name !== 'Tilem'),
        nationalHoliday,
        // Red-letter day: Sunday (weekly convention), a major Hindu holiday, or a fixed national holiday
        isRedDate: date.getDay() === 0 || isBigRahinan || !!nationalHoliday,
    };
}

// All notable days (Purnama/Tilem/Rahinan/hari libur) in a calendar month, sorted by date.
// Note: rahinan labels come straight from the library's generic Rahinan name (e.g. "Buda Cemeng"),
// not the wuku-qualified traditional variant (e.g. "Buda Cemeng Merakih").
export function getMonthRahinan(year, monthIndex) {
    const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
    const results = [];

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, monthIndex, day);
        const info = getBalineseInfo(date);
        if (info.isPurnama || info.isTilem || info.rahinan.length || info.nationalHoliday) {
            results.push({ date, ...info });
        }
    }

    return results;
}
