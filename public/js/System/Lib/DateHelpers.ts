export function months(year = null) {
  if (year == null) {
    year = new Date().getFullYear();
  }
  return {
    1: {
      name: 'January',
      firstDayOfWeek: new Date(year, 0, 1).getDay(),
      length: 31,
    },
    2: {
      name: 'February',
      firstDayOfWeek: new Date(year, 1, 1).getDay(),
      length: year % 4 === 0 ? 29 : 28,
    },
    3: {
      name: 'March',
      firstDayOfWeek: new Date(year, 2, 1).getDay(),
      length: 31,
    },
    4: {
      name: 'April',
      firstDayOfWeek: new Date(year, 3, 1).getDay(),
      length: 30,
    },
    5: {
      name: 'May',
      firstDayOfWeek: new Date(year, 4, 1).getDay(),
      length: 31,
    },
    6: {
      name: 'June',
      firstDayOfWeek: new Date(year, 5, 1).getDay(),
      length: 30,
    },
    7: {
      name: 'July',
      firstDayOfWeek: new Date(year, 6, 1).getDay(),
      length: 31,
    },
    8: {
      name: 'August',
      firstDayOfWeek: new Date(year, 7, 1).getDay(),
      length: 31,
    },
    9: {
      name: 'September',
      firstDayOfWeek: new Date(year, 8, 1).getDay(),
      length: 30,
    },
    10: {
      name: 'October',
      firstDayOfWeek: new Date(year, 9, 1).getDay(),
      length: 31,
    },
    11: {
      name: 'November',
      firstDayOfWeek: new Date(year, 10, 1).getDay(),
      length: 30,
    },
    12: {
      name: 'December',
      firstDayOfWeek: new Date(year, 11, 1).getDay(),
      length: 31,
    },
  };
}
