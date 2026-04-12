import React, { useState } from 'react';
import './StoikCodeWidget.css';

const CODES = [
  {
    id: '01',
    title: 'Endure With Purpose',
    body: 'Hard work is not random. It is directed. This means choosing discomfort that builds something. Training, recovery, and discipline all have intent behind them.',
  },
  {
    id: '02',
    title: 'Honor the Reps',
    body: 'Execution matters more than intensity. Every rep is done with control, awareness, and consistency. Sloppy work does not count. Precision builds performance.',
  },
  {
    id: '03',
    title: 'Lead With Intention',
    body: 'Nothing is done passively. Movement, breath, and effort are all deliberate. You are responsible for how you show up and how you train.',
  },
  {
    id: '04',
    title: 'Elevate the Standard',
    body: 'Average is not acceptable. This applies to technique, effort, recovery, and mindset. The expectation is consistent improvement, not occasional effort.',
  },
  {
    id: '05',
    title: 'Plan to Win',
    body: 'Results are built through preparation. Structured training, structured recovery, and clear progression. Nothing is left to chance.',
  },
];

export default function StoikCodeWidget() {
  const [openId, setOpenId] = useState(null);

  const toggle = (id) => {
    setOpenId((prev) => (prev === id ? null : id));
  };

  return (
    <div className="sc-widget" role="region" aria-label="The Stoik Code">
      <div className="sc-widget__header">
        <span className="sc-widget__badge">
          <span className="sc-widget__badge-dot" aria-hidden="true" />
          STOIK SYSTEM — ACTIVE
        </span>
        <h2 className="sc-widget__title">
          THE <span>STOIK</span> CODE
        </h2>
        <p className="sc-widget__sub">A standard. Not a statement.</p>
      </div>

      <div className="sc-widget__list" role="list">
        {CODES.map((code) => {
          const isOpen = openId === code.id;
          return (
            <div
              key={code.id}
              className={`sc-item${isOpen ? ' sc-item--open' : ''}`}
              role="listitem"
            >
              <button
                className="sc-item__trigger"
                onClick={() => toggle(code.id)}
                aria-expanded={isOpen}
                aria-controls={`sc-body-${code.id}`}
              >
                <span className="sc-item__num" aria-hidden="true">
                  {code.id}
                </span>
                <span className="sc-item__name">{code.title}</span>
                <span className="sc-item__icon" aria-hidden="true">
                  +
                </span>
              </button>

              <div
                id={`sc-body-${code.id}`}
                className={`sc-item__body${isOpen ? ' sc-item__body--open' : ''}`}
              >
                <div className="sc-item__body-inner">
                  <p className="sc-item__text">{code.body}</p>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      <div className="sc-widget__footer">
        <span className="sc-widget__footer-text">BUILT TO LAST</span>
        <span className="sc-widget__footer-mark">STOIK</span>
      </div>
    </div>
  );
}
