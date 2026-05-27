import React, { useState } from 'react';
import './WhatShouldWeMake.css';

const IDEAS = [
  {
    id: 'taco-cat-drone',
    name: 'Taco Cat Drone',
    vibe: 'Snack-powered chaos',
    build: 'A tiny game where a taco cat drops compliments from the sky.',
    step: 'Sketch the flying taco cat and pick three ridiculous power-ups.',
  },
  {
    id: 'pocket-weather-wizard',
    name: 'Pocket Weather Wizard',
    vibe: 'Useful, but magical',
    build: 'A weather card that gives outfit advice like a tiny dramatic wizard.',
    step: 'Make one card for sunny, rainy, windy, and suspiciously foggy.',
  },
  {
    id: 'tiny-chaos-museum',
    name: 'Tiny Chaos Museum',
    vibe: 'Curated nonsense',
    build: 'A gallery of weird internet artifacts with serious museum captions.',
    step: 'Create three exhibits and give each one a grand curator note.',
  },
  {
    id: 'compliment-cannon',
    name: 'Compliment Cannon',
    vibe: 'Instant serotonin',
    build: 'A button that launches custom compliments like confetti.',
    step: 'Write ten compliments that feel specific, warm, and a little silly.',
  },
  {
    id: 'mini-mission-machine',
    name: 'Mini Mission Machine',
    vibe: 'Adventure dispenser',
    build: 'A generator that gives you one playful quest for the next ten minutes.',
    step: 'Invent five quests that can happen at a desk, couch, or sidewalk.',
  },
  {
    id: 'no-boring-generator',
    name: 'No Boring Generator',
    vibe: 'Prototype playground',
    build: 'A page that transforms boring ideas into stranger, shinier versions.',
    step: 'Add sliders for weirdness, usefulness, speed, and sparkle.',
  },
];

export default function WhatShouldWeMake() {
  const [batchIndex, setBatchIndex] = useState(0);
  const [selectedId, setSelectedId] = useState(null);

  const visibleIdeas = [0, 1, 2].map((offset) => IDEAS[(batchIndex + offset) % IDEAS.length]);
  const selectedIdea = IDEAS.find((idea) => idea.id === selectedId);

  const pressIdeaButton = () => {
    setBatchIndex((currentIndex) => (currentIndex + 3) % IDEAS.length);
    setSelectedId(null);
  };

  return (
    <section className="wsm-machine" aria-labelledby="wsm-title">
      <div className="wsm-machine__topper">
        <div className="wsm-burst" aria-hidden="true">
          <span className="wsm-burst__hand">HI</span>
        </div>

        <button className="wsm-lever" type="button" onClick={pressIdeaButton}>
          <span className="wsm-lever__arm" aria-hidden="true" />
          <span className="wsm-lever__copy">Press here for your idea</span>
        </button>
      </div>

      <div className="wsm-machine__marquee">
        <span className="wsm-sign wsm-sign--left">Idea scanner</span>
        <div>
          <p className="wsm-machine__eyebrow">Half-baked ideas, fully fun prototypes</p>
          <h2 id="wsm-title" className="wsm-machine__title">
            What should we make?
          </h2>
        </div>
        <span className="wsm-sign wsm-sign--right">No boring stuff allowed</span>
      </div>

      <div className="wsm-factory" aria-live="polite">
        <div className="wsm-claw" aria-hidden="true">
          <span />
        </div>

        <div className="wsm-conveyor">
          {visibleIdeas.map((idea) => {
            const isSelected = selectedId === idea.id;

            return (
              <article
                className={`wsm-card${isSelected ? ' wsm-card--selected' : ''}`}
                key={idea.id}
              >
                <span className="wsm-card__label">{idea.vibe}</span>
                <h3 className="wsm-card__title">{idea.name}</h3>
                <p className="wsm-card__body">{idea.build}</p>
                <p className="wsm-card__step">
                  <span>First tiny step:</span> {idea.step}
                </p>
                <button
                  className="wsm-card__button"
                  type="button"
                  onClick={() => setSelectedId(idea.id)}
                  aria-pressed={isSelected}
                >
                  Make this now
                </button>
              </article>
            );
          })}
        </div>
      </div>

      <div className="wsm-machine__footer">
        <div className="wsm-ticket" aria-live="polite">
          <span className="wsm-ticket__label">Your idea:</span>
          <strong>{selectedIdea ? selectedIdea.name : 'Pull the lever, pick a winner'}</strong>
        </div>
        <button className="wsm-machine__button" type="button" onClick={pressIdeaButton}>
          Surprise us
        </button>
      </div>
    </section>
  );
}
