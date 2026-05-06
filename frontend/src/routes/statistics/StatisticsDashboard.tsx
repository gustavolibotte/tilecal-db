import React from 'react';
import { InventoryByTypeWidget } from './components/InventoryByTypeWidget';
import { LocationDistributionWidget } from './components/LocationDistributionWidget';
import { InstituteDistributionWidget } from './components/InstituteDistributionWidget';
import { OwnerDistributionWidget } from './components/OwnerDistributionWidget';
import { StatusDistributionWidget } from './components/StatusDistributionWidget';
import { OrphanedComponentsWidget } from './components/OrphanedComponentsWidget';

export const StatisticsDashboard: React.FC = () => {
  return (
    <div className="statistics-dashboard" style={{ padding: '2rem' }}>
      <h2 style={{ marginBottom: '2rem' }}>Statistics Dashboard</h2>
      
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(3, 1fr)',
        gap: '20px'
      }}>
        <InventoryByTypeWidget />
        <LocationDistributionWidget />
        <StatusDistributionWidget />
        <InstituteDistributionWidget />
        <OwnerDistributionWidget />
        <OrphanedComponentsWidget />
      </div>
    </div>
  );
};