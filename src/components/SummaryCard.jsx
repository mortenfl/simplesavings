export default function SummaryCard({ label, value, highlight }) {
  return (
    <div className={`summary-card${highlight ? ' summary-card--highlight' : ''}`}>
      <span className="summary-card__label">{label}</span>
      <span className="summary-card__value">{value}</span>
    </div>
  );
}
