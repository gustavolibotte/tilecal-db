import React from 'react';
import { InventoryByTypeWidget } from './components/InventoryByTypeWidget';
import { StatusDistributionWidget } from './components/StatusDistributionWidget';
import { LocationDistributionWidget } from './components/LocationDistributionWidget';
import { InstituteDistributionWidget } from './components/InstituteDistributionWidget';
// Importar o novo widget
import { OrphanedComponentsWidget } from './components/OrphanedComponentsWidget';

export const StatisticsDashboard: React.FC = () => {
  return (
    <div style={{ padding: '2rem' }}>
      <h1 style={{ color: '#0053a1', marginBottom: '2rem' }}>Phase II Upgrade Dashboard</h1>
      
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(450px, 1fr))', gap: '2rem', marginBottom: '2rem' }}>
          <InventoryByTypeWidget />
          <StatusDistributionWidget />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(450px, 1fr))', gap: '2rem', marginBottom: '2rem' }}>
          <LocationDistributionWidget />
          <InstituteDistributionWidget />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '2rem' }}>
          <OrphanedComponentsWidget />
      </div>
    </div>
  );
};