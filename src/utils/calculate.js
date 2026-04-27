export function calculateGrowth(startbelopp, manatligtTillskott, arsranta, antalAr) {
  const data = [];
  const monthlyRate = arsranta / 100 / 12;
  const months = antalAr * 12;

  let balance = startbelopp;
  let totalInsatt = startbelopp;

  data.push({ year: 0, totalValue: startbelopp, insatt: startbelopp });

  for (let month = 1; month <= months; month++) {
    if (arsranta === 0) {
      balance += manatligtTillskott;
    } else {
      balance = balance * (1 + monthlyRate) + manatligtTillskott;
    }
    totalInsatt += manatligtTillskott;

    if (month % 12 === 0) {
      data.push({
        year: month / 12,
        totalValue: balance,
        insatt: totalInsatt,
      });
    }
  }

  return data;
}
