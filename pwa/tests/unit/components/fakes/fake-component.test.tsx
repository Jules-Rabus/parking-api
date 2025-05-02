// @TODO: Remove this file when we have real tests to run

import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { FakeComponent } from '@/components/fakes/Fake';

describe('FakeComponent', () => {
  it('renders the dummy component text', () => {
    render(<FakeComponent />);
    const fakeElement = screen.getByTestId('fake');
    expect(fakeElement).toBeInTheDocument();
    expect(fakeElement).toHaveTextContent("I'm just a dummy component");
  });
});
