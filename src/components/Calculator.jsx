import { useState, useMemo } from 'react';
import SummaryCard from './SummaryCard';
import GrowthChart from './GrowthChart';
import { calculateGrowth } from '../utils/calculate';

const fmt = (n) => Math.round(n).toLocaleString('sv-SE') + ' kr';

function SliderField({ label, value, min, max, step, onChange, displayValue }) {
  return (
    <div className="field">
      <div className="field__header">
        <label className="field__label">{label}</label>
        <span className="field__value">{displayValue}</span>
      </div>
      <input
        type="range"
        min={min}
        max={max}
        step={step}
        value={value}
        onChange={(e) => onChange(Number(e.target.value))}
        className="slider"
      />
      <div className="field__range">
        <span>{min}%</span>
        <span>{max}%</span>
      </div>
    </div>
  );
}

function NumberField({ label, value, onChange, prefix }) {
  const [raw, setRaw] = useState('');
  const [focused, setFocused] = useState(false);

  const displayVal = focused ? raw : value === 0 ? '' : value.toLocaleString('sv-SE');

  const handleFocus = () => {
    setFocused(true);
    setRaw(value === 0 ? '' : String(value));
  };

  const handleBlur = () => {
    setFocused(false);
    const parsed = parseInt(raw.replace(/\s/g, ''), 10);
    onChange(isNaN(parsed) || parsed < 0 ? 0 : parsed);
  };

  const handleChange = (e) => {
    const v = e.target.value.replace(/[^0-9]/g, '');
    setRaw(v);
  };

  return (
    <div className="field">
      <label className="field__label">{label}</label>
      <div className="input-wrapper">
        {prefix && <span className="input-prefix">{prefix}</span>}
        <input
          type="text"
          inputMode="numeric"
          className="text-input"
          value={displayVal}
          onFocus={handleFocus}
          onBlur={handleBlur}
          onChange={handleChange}
          placeholder="0"
        />
        <span className="input-suffix">kr</span>
      </div>
    </div>
  );
}

function YearSlider({ label, value, min, max, onChange }) {
  return (
    <div className="field">
      <div className="field__header">
        <label className="field__label">{label}</label>
        <span className="field__value">{value} år</span>
      </div>
      <input
        type="range"
        min={min}
        max={max}
        step={1}
        value={value}
        onChange={(e) => onChange(Number(e.target.value))}
        className="slider"
      />
      <div className="field__range">
        <span>{min} år</span>
        <span>{max} år</span>
      </div>
    </div>
  );
}

export default function Calculator() {
  const [startbelopp, setStartbelopp] = useState(50000);
  const [manatligtTillskott, setManatligtTillskott] = useState(2000);
  const [arsranta, setArsranta] = useState(7);
  const [antalAr, setAntalAr] = useState(20);

  const data = useMemo(
    () => calculateGrowth(startbelopp, manatligtTillskott, arsranta, antalAr),
    [startbelopp, manatligtTillskott, arsranta, antalAr]
  );

  const last = data[data.length - 1];
  const slutvarde = last.totalValue;
  const insatt = last.insatt;
  const rantevinst = slutvarde - insatt;

  return (
    <div className="calculator">
      <div className="calculator__inputs">
        <h2 className="section-title">Inställningar</h2>

        <NumberField
          label="Startbelopp"
          value={startbelopp}
          onChange={setStartbelopp}
        />

        <NumberField
          label="Månatligt tillskott"
          value={manatligtTillskott}
          onChange={setManatligtTillskott}
        />

        <SliderField
          label="Årsränta"
          value={arsranta}
          min={0}
          max={20}
          step={0.5}
          onChange={setArsranta}
          displayValue={`${arsranta.toLocaleString('sv-SE')} %`}
        />

        <YearSlider
          label="Antal år"
          value={antalAr}
          min={1}
          max={40}
          onChange={setAntalAr}
        />
      </div>

      <div className="calculator__results">
        <h2 className="section-title">Resultat</h2>

        <div className="summary-cards">
          <SummaryCard label="Slutvärde" value={fmt(slutvarde)} highlight />
          <SummaryCard label="Insatt" value={fmt(insatt)} />
          <SummaryCard label="Räntevinst" value={fmt(rantevinst)} />
        </div>

        <GrowthChart data={data} />
      </div>
    </div>
  );
}
