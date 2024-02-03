import { Button } from "primereact/button";
import { useNavigate } from "react-router-dom";
import './home_button.css';

export default function HomeButton() {
  const navigate = useNavigate();
  return (
    <Button
    className="p-menuitem-text text-xl font-normal"
    label="Tilecal Electronics Database"
    onClick={() => navigate('/')} link/>
  );
}
