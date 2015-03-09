<?hh

class :test:any-children extends :x:element {
  children any;
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:no-children extends :x:element {
  children empty;
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:single-child extends :x:element {
  children (:div);
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:optional-child extends :x:element {
  children (:div?);
  protected function render(): XHPRoot {
    return <div />;
  }
}


class :test:any-number-of-child extends :x:element {
  children (:div*);
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:at-least-one-child extends :x:element {
  children (:div+);
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:two-children extends :x:element {
  children (:div, :div);
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:either-of-two-children extends :x:element {
  children (:div | :code);
  protected function render(): XHPRoot {
    return <div />;
  }
}

class :test:nested-rule extends :x:element {
  children ( :div | (:code+));
  protected function render(): XHPRoot {
    return <div />;
  }
}

class ChildRuleTest extends PHPUnit_Framework_TestCase {
  public function testNoChild(): void {
    $elems = Vector {
      <test:no-children />,
      <test:any-children />,
      <test:optional-child />,
      <test:any-number-of-child />,
    };
    foreach ($elems as $elem) {
      $this->assertSame('<div></div>', (string) $elem);
    }
  }

  /**
   * @expectedException XHPInvalidChildrenException
   */
  public function testUnexpectedChild(): void {
    $x = <test:no-children><div /></test:no-children>;
    $x->toString();
  }

  public function testSingleChild(): void {
    $elems = Vector {
       <test:any-children />,
       <test:single-child />,
       <test:optional-child />,
       <test:any-number-of-child />,
       <test:at-least-one-child />,
       <test:either-of-two-children />,
       <test:nested-rule />,
    };
    foreach ($elems as $elem) {
      $elem->appendChild(<div>Foo</div>);
      $this->assertSame('<div></div>', (string) $elem);
    }
  }

  public function testExpectedChild(): void {
    $elems = Vector {
       <test:single-child />,
       <test:at-least-one-child />,
       <test:either-of-two-children />,
       <test:nested-rule />,
    };
    foreach ($elems as $elem) {
      $exception = null;
      try {
        $elem->toString();
      } catch (Exception $e) {
        $exception = $e;
      }
      $this->assertInstanceOf(XHPInvalidChildrenException::class, $exception);
    }
  }

  public function testTooManyChildren(): void {
    $elems = Vector {
      <test:single-child />,
      <test:optional-child />,
      <test:two-children />,
      <test:either-of-two-children />,
      <test:nested-rule />,
    };
    foreach ($elems as $elem) {
      $exception = null;
      $elem->appendChild(<x:frag><div /><div /><div /></x:frag>);
      try {
        $elem->toString();
      } catch (Exception $e) {
        $exception = $e;
      }
      $this->assertInstanceOf(XHPInvalidChildrenException::class, $exception);
    }
  }

  public function testIncorrectChild(): void {
    $elems = Vector {
      <test:single-child />,
      <test:optional-child />,
      <test:any-number-of-child />,
      <test:at-least-one-child />,
      <test:either-of-two-children />,
      <test:nested-rule />,
    };
    foreach ($elems as $elem) {
      $exception = null;
      $elem->appendChild(<span />);
      try {
        $elem->toString();
      } catch (Exception $e) {
        $exception = $e;
      }
      $this->assertInstanceOf(XHPInvalidChildrenException::class, $exception);
    }
  }

  public function testTwoChildren(): void {
    $elems = Vector {
      <test:any-number-of-child />,
      <test:at-least-one-child />,
      <test:two-children />,
    };
    foreach ($elems as $elem) {
      $elem->appendChild(<x:frag><div /><div /></x:frag>);
      $this->assertSame('<div></div>', $elem->toString());
    }
  }

  public function testThreeChildren(): void {
    $elems = Vector {
      <test:any-number-of-child />,
      <test:at-least-one-child />,
    };
    foreach ($elems as $elem) {
      $elem->appendChild(<x:frag><div /><div /><div /></x:frag>);
      $this->assertSame('<div></div>', $elem->toString());
    }
  }

  public function testEitherValidChild(): void {
    $x = <test:either-of-two-children><div /></test:either-of-two-children>;
    $this->assertSame('<div></div>', $x->toString());
    $x = <test:either-of-two-children><code /></test:either-of-two-children>;
    $this->assertSame('<div></div>', $x->toString());

    $x = <test:nested-rule><div /></test:nested-rule>;
    $this->assertSame('<div></div>', $x->toString());
    $x = <test:nested-rule><code /></test:nested-rule>;
    $this->assertSame('<div></div>', $x->toString());
    $x = <test:nested-rule><code /><code /></test:nested-rule>;
    $this->assertSame('<div></div>', $x->toString());
  }
}
