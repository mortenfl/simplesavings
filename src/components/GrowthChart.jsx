import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const fmt = (n) => n.toLocaleString('sv-SE', { maximumFractionDigits: 0 }) + ' kr';

export default function GrowthChart({ data }) {
  const labels = data.map((d) => `År ${d.year}`);

  const chartData = {
    labels,
    datasets: [
      {
        label: 'Totalt värde',
        data: data.map((d) => d.totalValue),
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.12)',
        fill: true,
        tension: 0.35,
        pointRadius: data.length <= 15 ? 4 : 2,
      },
      {
        label: 'Insatt kapital',
        data: data.map((d) => d.insatt),
        borderColor: '#64748b',
        backgroundColor: 'rgba(100, 116, 139, 0.08)',
        fill: true,
        tension: 0.35,
        borderDash: [5, 4],
        pointRadius: data.length <= 15 ? 4 : 2,
      },
    ],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { position: 'top' },
      tooltip: {
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${fmt(ctx.parsed.y)}`,
        },
      },
    },
    scales: {
      y: {
        ticks: {
          callback: (v) => v.toLocaleString('sv-SE', { maximumFractionDigits: 0 }) + ' kr',
        },
        grid: { color: 'rgba(0,0,0,0.06)' },
      },
      x: { grid: { display: false } },
    },
  };

  return (
    <div className="chart-wrapper">
      <Line data={chartData} options={options} />
    </div>
  );
}
