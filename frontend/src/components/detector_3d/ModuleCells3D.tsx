import React, { useMemo, useState } from 'react';
import { Center } from '@react-three/drei';
import * as THREE from 'three';
import { LBA_CELLS, EBA_CELLS, EXTRUDE_SETTINGS } from './cellGeometry';

interface ModuleCells3DProps {
  barrel: string; // 'LBA', 'LBC', 'EBA', 'EBC'
  moduleNumber: string; // '01' to '64'
  onHoverCell: (cellData: any) => void;
}

export const ModuleCells3D: React.FC<ModuleCells3DProps> = ({ barrel, moduleNumber, onHoverCell }) => {
  const [hoveredCell, setHoveredCell] = useState<string | null>(null);

  const cellData = useMemo(() => {
    const isLongBarrel = barrel === 'LBA' || barrel === 'LBC';
    const shapes = isLongBarrel ? LBA_CELLS : EBA_CELLS;
    
    return Object.entries(shapes).map(([cellId, shape]) => {
      const isNominal = Math.random() > 0.15;
      return {
        id: cellId,
        shape: shape as THREE.Shape,
        status: isNominal ? 'NOMINAL' : 'ALERT',
        color: isNominal ? '#37c871' : '#e02f1f',
        temp: (20 + Math.random() * 15).toFixed(1) + ' °C',
        pmtHV: isNominal ? '800V' : '0V (Falha)'
      };
    });
  }, [barrel]);

  const isC_Side = barrel === 'LBC' || barrel === 'EBC';
  const scale: [number, number, number] = [isC_Side ? -1 : 1, -1, 1];

  return (
    <Center>
      <group scale={scale}>
        {cellData.map((cell) => (
          <mesh
            key={cell.id}
            onPointerOver={(e) => {
              e.stopPropagation();
              setHoveredCell(cell.id);
              onHoverCell({ barrel, moduleNumber, ...cell, clientX: e.clientX, clientY: e.clientY });
            }}
            onPointerMove={(e) => {
              e.stopPropagation();
              onHoverCell({ barrel, moduleNumber, ...cell, clientX: e.clientX, clientY: e.clientY });
            }}
            onPointerOut={(e) => {
              e.stopPropagation();
              setHoveredCell(null);
              onHoverCell(null);
            }}
          >
            <extrudeGeometry args={[cell.shape, EXTRUDE_SETTINGS]} />
            <meshStandardMaterial 
              color={hoveredCell === cell.id ? '#ffffff' : cell.color} 
              roughness={0.3}
              metalness={0.7}
              opacity={0.9}
              transparent
            />
            <lineSegments>
              <edgesGeometry args={[new THREE.ExtrudeGeometry(cell.shape, EXTRUDE_SETTINGS)]} />
              <lineBasicMaterial color="#141415" linewidth={1} />
            </lineSegments>
          </mesh>
        ))}
      </group>
    </Center>
  );
};