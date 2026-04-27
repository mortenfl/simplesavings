import Calculator from './components/Calculator';
import './App.css';

export default function App() {
  return (
    <div className="app">
      <header className="app-header">
        <h1>Ränta-på-ränta kalkylator</h1>
        <p>Beräkna hur ditt sparande växer över tid</p>
      </header>
      <main>
        <Calculator />
      </main>
    </div>
  );
}
